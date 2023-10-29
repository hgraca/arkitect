<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\E2E\PHPUnit;

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\Expression\ForClasses\HaveAttribute;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Rule;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @requires PHP >= 8.0
 */
final class CheckClassHaveAttributeTest extends TestCase
{
    public function test_entities_should_reside_in_app_model(): void
    {
        $set = ClassSet::fromDir(__DIR__.'/../_fixtures/mvc');

        $rule = Rule::allClasses()
            ->that(new HaveAttribute('Entity'))
            ->should(new ResideInOneOfTheseNamespaces('App\Model'))
            ->because('we use an ORM');

        ArchRuleTestCase::assertArchRule($rule, $set);
    }

    public function test_controllers_should_have_name_ending_in_controller(): void
    {
        $set = ClassSet::fromDir(__DIR__.'/../_fixtures/mvc');

        $rule = Rule::allClasses()
            ->that(new HaveAttribute('AsController'))
            ->should(new HaveNameMatching('*Controller'))
            ->because('its a symfony thing');

        $expectedExceptionMessage = '
App\Controller\Foo has 1 violations
  should have a name that matches *Controller
  because its a symfony thing';

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        ArchRuleTestCase::assertArchRule($rule, $set);
    }

    public function test_controllers_should_have_controller_attribute(): void
    {
        $set = ClassSet::fromDir(__DIR__.'/../_fixtures/mvc');

        $rule = Rule::allClasses()
            ->that(new HaveNameMatching('*Controller'))
            ->should(new HaveAttribute('AsController'))
            ->because('it configures the service container');

        ArchRuleTestCase::assertArchRule($rule, $set);
    }
}
