<?php

declare(strict_types=1);

namespace Darkheim\Application\View;

final class FormFieldRenderer
{
    public static function enabledisableCheckboxes(mixed $name, mixed $checked, mixed $enabledText, mixed $disabledText): void
    {
        $checkedValue = $checked instanceof \SimpleXMLElement ? (string) $checked : $checked;
        $normalized = in_array($checkedValue, [true, 1, '1', 'true'], true) ? '1' : '0';
        $fieldName  = (string) $name;

        echo '<div class="radio">';
        echo '<label class="radio">';
        echo '<input type="radio" name="' . htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') . '" value="1" ' . ($normalized === '1' ? 'checked' : '') . '>';
        echo htmlspecialchars((string) $enabledText, ENT_QUOTES, 'UTF-8');
        echo '</label>';
        echo '<label class="radio">';
        echo '<input type="radio" name="' . htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') . '" value="0" ' . ($normalized === '0' ? 'checked' : '') . '>';
        echo htmlspecialchars((string) $disabledText, ENT_QUOTES, 'UTF-8');
        echo '</label>';
        echo '</div>';
    }
}
