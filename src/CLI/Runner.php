<?php

declare(strict_types=1);

namespace Arkitect\CLI;

use Arkitect\Analyzer\ClassDescriptionCollection;
use Arkitect\Analyzer\ClassDescriptionRegistry;
use Arkitect\Analyzer\FileParserFactory;
use Arkitect\Analyzer\Parser;
use Arkitect\ClassSetRules;
use Arkitect\CLI\Progress\Progress;
use Arkitect\Exceptions\FailOnFirstViolationException;
use Arkitect\Rules\ParsingErrors;
use Arkitect\Rules\Violations;
use Symfony\Component\Finder\SplFileInfo;

class Runner
{
    private Violations $violations;

    private ParsingErrors $parsingErrors;

    private bool $stopOnFailure;

    private ClassDescriptionRegistry $classDescriptionRegistry;

    public function __construct(bool $stopOnFailure = false)
    {
        $this->stopOnFailure = $stopOnFailure;
        $this->violations = new Violations();
        $this->parsingErrors = new ParsingErrors();
        $this->classDescriptionRegistry = ClassDescriptionRegistry::new();
    }

    /**
     * @throws FailOnFirstViolationException
     */
    public function run(Config $config, Progress $progress, TargetPhpVersion $targetPhpVersion, bool $onlyErrors): void
    {
        $fileParser = FileParserFactory::createFileParser($targetPhpVersion, $config->isParseCustomAnnotationsEnabled());
        $this->classDescriptionRegistry = ClassDescriptionRegistry::new();

        /** @var ClassSetRules $classSetRule */
        foreach ($config->getClassSetRules() as $classSetRule) {
            if (!$onlyErrors) {
                $progress->startFileSetAnalysis($classSetRule->getClassSet());
            }

            $this->check($classSetRule, $progress, $fileParser, $this->violations, $this->parsingErrors, $onlyErrors);

            if (!$onlyErrors) {
                $progress->endFileSetAnalysis($classSetRule->getClassSet());
            }
        }
    }

    /**
     * @throws FailOnFirstViolationException
     */
    public function check(
        ClassSetRules $classSetRule,
        Progress $progress,
        Parser $fileParser,
        Violations $violations,
        ParsingErrors $parsingErrors,
        bool $onlyErrors = false
    ): void {
        $classDescriptions = $this->parseClassSet($classSetRule, $progress, $fileParser, $parsingErrors, $onlyErrors);

        $this->analyseClassSet($classSetRule, $classDescriptions, $violations, $progress, $onlyErrors);
    }

    public function getViolations(): Violations
    {
        return $this->violations;
    }

    public function getParsingErrors(): ParsingErrors
    {
        return $this->parsingErrors;
    }

    public function parseFile(
        Parser $fileParser,
        SplFileInfo $file,
        ParsingErrors $parsingErrors
    ): ClassDescriptionCollection {
        $fileParser->parse($file->getContents(), $file->getRelativePathname());
        $parsedErrors = $fileParser->getParsingErrors();

        foreach ($parsedErrors as $parsedError) {
            $parsingErrors->add($parsedError);
        }

        return $fileParser->getClassDescriptions();
    }

    private function parseClassSet(
        ClassSetRules $classSetRule,
        Progress $progress,
        Parser $fileParser,
        ParsingErrors $parsingErrors,
        bool $onlyErrors = false
    ): ClassDescriptionCollection {
        $classDescriptionCollection = new ClassDescriptionCollection();

        /** @var SplFileInfo $file */
        foreach ($classSetRule->getClassSet() as $file) {
            if (!$onlyErrors) {
                $progress->startParsingFile($file->getRelativePathname());
            }

            $classesParsedFromFile = new ClassDescriptionCollection();
            $initialParsingErrors = $parsingErrors->count();
            if (!$this->classDescriptionRegistry->hasFile($file->getRelativePathname())) {
                $classesParsedFromFile = $this->parseFile($fileParser, $file, $parsingErrors);
            }
            $finalParsingErrors = $parsingErrors->count();

            if ($initialParsingErrors === $finalParsingErrors && !$classesParsedFromFile->isEmpty()) {
                $this->classDescriptionRegistry->addCollection($classesParsedFromFile);
                $classDescriptionCollection->addCollection($classesParsedFromFile);
            }

            if (!$onlyErrors) {
                $progress->endParsingFile($file->getRelativePathname());
            }
        }

        return $classDescriptionCollection;
    }

    private function analyseClassSet(
        ClassSetRules $classSetRule,
        ClassDescriptionCollection $classDescriptionsCollection,
        Violations $violations,
        Progress $progress,
        bool $onlyErrors
    ): void {
        foreach ($classDescriptionsCollection as $classDescription) {
            $fileViolations = new Violations();
            if (!$onlyErrors) {
                $progress->startParsingFile($classDescription->getFilePath());
            }
            foreach ($classSetRule->getRules() as $rule) {
                $rule->check($classDescription, $fileViolations);

                if ($this->stopOnFailure && $fileViolations->count() > 0) {
                    $violations->merge($fileViolations);

                    throw new FailOnFirstViolationException();
                }
            }

            $violations->merge($fileViolations);

            if (!$onlyErrors) {
                $progress->endParsingFile($classDescription->getFilePath());
            }
        }
    }
}
