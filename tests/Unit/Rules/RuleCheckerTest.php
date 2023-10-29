<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Rules;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Analyzer\FileParserFactory;
use Modulith\ArchCheck\Analyzer\Parser;
use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\ClassSetRules;
use Modulith\ArchCheck\CLI\Progress\VoidProgress;
use Modulith\ArchCheck\CLI\Runner;
use Modulith\ArchCheck\CLI\TargetPhpVersion;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Expression\ForClasses\Implement;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\DSL\ArchRule;
use Modulith\ArchCheck\Rules\ParsingErrors;
use Modulith\ArchCheck\Rules\Rule;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\Violations;
use Modulith\ArchCheck\Test\Fixtures\Animal\AnimalInterface;
use Modulith\ArchCheck\Test\Fixtures\Fruit\AnimalFruit;
use Modulith\ArchCheck\Test\Fixtures\Fruit\CavendishBanana;
use Modulith\ArchCheck\Test\Fixtures\Fruit\DwarfCavendishBanana;
use Modulith\ArchCheck\Test\Fixtures\Fruit\FruitInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

class RuleCheckerTest extends TestCase
{
    public function test_should_run_parse_on_all_files_in_class_set(): void
    {
        $violations = new Violations();
        $fileParser = new FakeParser();
        $rule = new FakeRule();
        $parsingErrors = new ParsingErrors();

        $runner = new Runner();

        $runner->check(
            ClassSetRules::create(new FakeClassSet(), ...[$rule]),
            new VoidProgress(),
            $fileParser,
            $violations,
            $parsingErrors
        );

        self::assertCount(3, $violations);
    }

    public function test_can_exclude_files_or_directories_from_multiple_dir_class_set_with_no_violations(): void
    {
        $classSet = ClassSet::fromDir(\FIXTURES_PATH);

        $rules[] = Rule::allClasses()
            ->except(FruitInterface::class, CavendishBanana::class, DwarfCavendishBanana::class, AnimalFruit::class)
            ->that(new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\Fruit'))
            ->should(new Implement(FruitInterface::class))
            ->because('this tests that string exceptions fail');

        $rules[] = Rule::allClasses()
            ->exceptExpression(new HaveNameMatching('*TestCase'))
            ->that(new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\Animal'))
            ->should(new Implement(AnimalInterface::class))
            ->because('this tests that expression exceptions fail');

        $runner = new Runner();

        $runner->check(
            ClassSetRules::create($classSet, ...$rules),
            new VoidProgress(),
            FileParserFactory::createFileParser(TargetPhpVersion::create(null)),
            $violations = new Violations(),
            new ParsingErrors()
        );

        self::assertCount(0, $violations);
    }

    public function test_can_exclude_files_or_directories_from_multiple_dir_class_set_with_violations(): void
    {
        $classSet = ClassSet::fromDir(\FIXTURES_PATH);

        $rules[] = Rule::allClasses()
            ->except(FruitInterface::class, CavendishBanana::class, AnimalFruit::class)
            ->that(new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\Fruit'))
            ->should(new Implement(FruitInterface::class))
            ->because('this tests that string exceptions fail');

        $rules[] = Rule::allClasses()
            ->exceptExpression(new HaveNameMatching('*NotExistingSoItFails'))
            ->that(new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\Animal'))
            ->should(new Implement(AnimalInterface::class))
            ->because('this tests that expression exceptions fail');

        $runner = new Runner();

        $runner->check(
            ClassSetRules::create($classSet, ...$rules),
            new VoidProgress(),
            FileParserFactory::createFileParser(TargetPhpVersion::create(null)),
            $violations = new Violations(),
            new ParsingErrors()
        );

        self::assertCount(2, $violations);
        $expectedViolations = "Modulith\ArchCheck\Test\Fixtures\Animal\CatTestCase has 1 violations
            should implement Modulith\ArchCheck\Test\Fixtures\Animal\AnimalInterface because this tests
            that expression exceptions fail Modulith\ArchCheck\Test\Fixtures\Fruit\DwarfCavendishBanana has 1 violations
            should implement Modulith\ArchCheck\Test\Fixtures\Fruit\FruitInterface because
            this tests that string exceptions fail";
        self::assertEquals(
            preg_replace('/\s+/', ' ', $expectedViolations),
            preg_replace('/\s+/', ' ', trim($violations->toString()))
        );
    }
}

class FakeClassSet extends ClassSet
{
    public function __construct()
    {
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([
            new FakeSplFileInfo('uno', '.', 'dir'),
            new FakeSplFileInfo('due', '.', 'dir'),
            new FakeSplFileInfo('tre', '.', 'dir'),
        ]);
    }
}

class FakeSplFileInfo extends SplFileInfo
{
    public function getContents(): string
    {
        return '';
    }
}

class FakeRule implements ArchRule
{
    public function check(ClassDescription $classDescription, Violations $violations): void
    {
        $violations->add(new Violation('fqcn', 'error'));
    }

    public function isRunOnlyThis(): bool
    {
        return false;
    }

    public function runOnlyThis(): ArchRule
    {
        return $this;
    }
}

class FakeParser implements Parser
{
    public function parse(string $fileContent, string $filename): void
    {
    }

    public function getClassDescriptions(): array
    {
        return [ClassDescription::getBuilder('uno')->build()];
    }

    public function getParsingErrors(): array
    {
        return [];
    }
}
