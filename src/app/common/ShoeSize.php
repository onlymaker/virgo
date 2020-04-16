<?php

namespace app\common;

class ShoeSize
{
    const US = 'US';
    const EU = 'EU';
    const UK = 'UK';

    function available()
    {
        return [
            'US' => [
                0 => 'US5',
                1 => 'US6',
                2 => 'US7',
                3 => 'US8',
                4 => 'US9',
                5 => 'US9.5',
                6 => 'US10',
                7 => 'US11',
                8 => 'US12',
                9 => 'US13',
                10 => 'US14',
                11 => 'US15',
            ],
            'EU' => [
                0 => 'EU35',
                1 => 'EU36',
                2 => 'EU37',
                3 => 'EU38',
                4 => 'EU39',
                5 => 'EU40',
                6 => 'EU41',
                7 => 'EU42',
                8 => 'EU43',
                9 => 'EU44',
                10 => 'EU45',
                11 => 'EU46',
            ],
            'UK' => [
                0 => 'UK2',
                1 => 'UK3',
                2 => 'UK4',
                3 => 'UK5',
                4 => 'UK6',
                5 => 'UK7',
                6 => 'UK8',
                7 => 'UK9',
                8 => 'UK10',
                9 => 'UK11',
                10 => 'UK12',
                11 => 'UK13',
            ],
        ];
    }

    function normalize(string $size): array
    {
        return [
            'US' => $this->convert($size, ShoeSize::US),
            'EU' => $this->convert($size, ShoeSize::EU),
            'UK' => $this->convert($size, ShoeSize::UK),
        ];
    }

    function convert(string $fromSize, string $toSystem): string
    {
        $size = strtoupper($fromSize);
        $from = substr($size, 0, 2);
        $to = strtoupper($toSystem);
        if ($from == $to) {
            return $size;
        } else {
            $available = $this->available();
            if (isset($available[$from]) && isset($available[$to])) {
                if (($i = array_search($size, $available[$from])) !== false) {
                    return $available[$to][$i];
                }
            }
        }
        return $to;
    }

    function compare($x, $y)
    {
        $pattern = '/(?<size>\d+\.?\d?)/';
        preg_match($pattern, $x, $matches);
        $xSize = $matches['size'] ?? 0;
        preg_match($pattern, $y, $matches);
        $ySize = $matches['size'] ?? 0;
        return $xSize - $ySize;
    }
}