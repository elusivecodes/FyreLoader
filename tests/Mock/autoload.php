<?php

namespace Tests\Mock;

class MockComposer
{

    public function getClassMap(): array
    {
        return [];
    }

    public function getPrefixesPsr4(): array
    {
        return [
            'Fyre' => 'src/'
        ];
    }

}

return new MockComposer();
