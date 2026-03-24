<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Admincp\Support;

use Darkheim\Application\Admincp\Support\ModuleConfigCatalog;
use PHPUnit\Framework\TestCase;

final class ModuleConfigCatalogTest extends TestCase
{
    public function testDefinitionReturnsNullForUnknownKey(): void
    {
        $catalog = new ModuleConfigCatalog();

        $this->assertNull($catalog->definition('does-not-exist'));
    }

    public function testDefinitionReturnsExpectedXmlAndFields(): void
    {
        $catalog = new ModuleConfigCatalog();

        $definition = $catalog->definition('login');

        $this->assertIsArray($definition);
        $this->assertSame('login.xml', $definition['xml']);
        $this->assertSame('active', $definition['fields']['setting_1']);
        $this->assertSame('failed_login_timeout', $definition['fields']['setting_5']);
    }

    public function testModuleConfigNameFromKeyMapsLegacyAliases(): void
    {
        $catalog = new ModuleConfigCatalog();

        $this->assertSame('forgot-password', $catalog->moduleConfigNameFromKey('forgotpassword'));
        $this->assertSame('donation-paypal', $catalog->moduleConfigNameFromKey('paypal'));
        $this->assertSame('rankings', $catalog->moduleConfigNameFromKey('rankings'));
    }
}
