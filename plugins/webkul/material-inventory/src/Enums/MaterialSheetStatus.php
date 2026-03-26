<?php

namespace Webkul\MaterialInventory\Enums;

enum MaterialSheetStatus: string
{
    case Nuovo = 'nuovo';
    case Usato = 'usato';
    case Guasto = 'guasto';
    case InUso = 'in_uso';
    case InRiparazione = 'in_riparazione';

    public function excelLabel(): string
    {
        return match ($this) {
            self::Nuovo         => 'New',
            self::Usato         => 'Used',
            self::Guasto        => 'Broken',
            self::InUso         => 'In use',
            self::InRiparazione => 'Under repair',
        };
    }
}
