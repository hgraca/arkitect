<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\E2E\PHPUnit;

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Rule;
use PHPUnit\Framework\TestCase;

class CheckClassNamingTest extends TestCase
{
    public function test_code_in_happy_island_should_have_name_matching_prefix(): void
    {
        $set = ClassSet::fromDir(__DIR__.'/../_fixtures/happy_island');

        $rule = Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\HappyIsland'))
            ->should(new HaveNameMatching('Happy*'))
            ->because("that's what she said");

        ArchRuleTestCase::assertArchRule($rule, $set);
    }
}
