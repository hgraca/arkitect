<?php

declare(strict_types=1);

namespace Arkitect\RuleBuilders\Layers;

use Arkitect\Expression\Expression;
use Arkitect\RuleBuilders\Architecture\Architecture;
use Arkitect\RuleBuilders\RuleSetBuilderInterface;

final class LayerDependenciesGoInwardsAndDownwards implements RuleSetBuilderInterface
{
    private Expression $domain;
    private Expression $application;
    private Expression $useCase;
    private Expression $sharedKernel;
    private Expression $port;
    private Expression $adapter;
    private Expression $phpOverlay;
    private Expression $codeConfig;
    private Expression $conformistDependencies;
    private Expression $vendor;
    private Expression $presentation;
    private Expression $tests;

    public function __construct(
        Expression $domain,
        Expression $application,
        Expression $useCase,
        Expression $sharedKernel,
        Expression $port,
        Expression $adapter,
        Expression $phpOverlay,
        Expression $codeConfig,
        Expression $conformistDependencies,
        Expression $vendor,
        Expression $presentation,
        Expression $tests
    ) {
        $this->domain = $domain;
        $this->application = $application;
        $this->useCase = $useCase;
        $this->sharedKernel = $sharedKernel;
        $this->port = $port;
        $this->adapter = $adapter;
        $this->phpOverlay = $phpOverlay;
        $this->codeConfig = $codeConfig;
        $this->conformistDependencies = $conformistDependencies;
        $this->vendor = $vendor;
        $this->presentation = $presentation;
        $this->tests = $tests;
    }

    public function build(): array
    {
        $iterableRules = Architecture::withComponents()
            ->component('Domain')->definedByExpression($this->domain)
            ->component('Application')->definedByExpression($this->application)
            ->component('UseCase')->definedByExpression($this->useCase)
            ->component('SharedKernel')->definedByExpression($this->sharedKernel)
            ->component('Port')->definedByExpression($this->port)
            ->component('Adapter')->definedByExpression($this->adapter)
            ->component('PhpOverlay')->definedByExpression($this->phpOverlay)
            ->component('CodeConfig')->definedByExpression($this->codeConfig)
            ->component('ConformistDependencies')->definedByExpression($this->conformistDependencies)
            ->component('Vendor')->definedByExpression($this->vendor)
            ->component('Presentation')->definedByExpression($this->presentation)
            ->component('Tests')->definedByExpression($this->tests)
            ->where('PhpOverlay')->mayDependOnComponents('ConformistDependencies')
            ->where('Domain')->mayDependOnComponents('PhpOverlay', 'ConformistDependencies', 'SharedKernel')
            ->where('UseCase')->mayDependOnComponents(
                'Domain',
                'Application',
                'Port',
                'SharedKernel',
                'PhpOverlay',
                'ConformistDependencies',
            )
            ->where('Application')->mayDependOnComponents(
                'Domain',
                'UseCase',
                'Port',
                'SharedKernel',
                'PhpOverlay',
                'ConformistDependencies',
            )
            ->where('Port')->mayDependOnComponents('PhpOverlay', 'ConformistDependencies', 'SharedKernel')
            ->where('Adapter')->mayDependOnComponents(
                'UseCase',
                'Port',
                'Vendor',
                'PhpOverlay',
                'ConformistDependencies',
                'SharedKernel',
            )
            ->where('Ui')->mayDependOnComponents(
                'Domain',
                'Application',
                'Port',
                'PhpOverlay',
                'ConformistDependencies',
            )
            ->where('CodeConfig')->shouldOnlyDependOnComponents(
                'Application',
                'Domain',
                'Adapter',
                'Port',
                'Vendor',
                'PhpOverlay',
                'ConformistDependencies',
            )
            ->where('Tests')->mayDependOnAnyComponent()
            ->rules('layers dependency direction should only be inwards');

        $rulesList = [];
        foreach ($iterableRules as $rule) {
            $rulesList[] = $rule;
        }

        return $rulesList;
    }
}
