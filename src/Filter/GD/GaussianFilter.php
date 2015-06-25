<?php

namespace Imanee\Filter\GD;

use Imanee\Imanee;
use Imanee\Model\FilterInterface;
use Imanee\ImageResource\GDResource;

class GaussianFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Imanee $imanee, array $options = [])
    {
        /** @var resource $resource */
        $size = $imanee->getSize();
        $options = array_merge(['radius' => 2, 'sigma' => 2], $options);

        $offset = floor($options['radius'] / 2);
        $radius = $offset * 2;
        $transparency = ceil(100 / ($radius + 1));
        $canvas = new GDResource();
        $canvas->createNew($size['width'] + $radius, $size['height'] + $radius);

        for ($x=0; $x<$radius; $x++) {
            for ($y=0; $y<$radius; $y++) {
                imagecopymerge(
                    $canvas->getResource(),
                    $imanee->getResource()->getResource(),
                    $x,
                    $y,
                    0,
                    0,
                    $size['width'],
                    $size['height'],
                    $x == $offset && $y == $offset ? $transparency * 2 : $transparency);
            }
        }
        // Trim off our extended boundaries
        $canvas->crop($size['width'], $size['height'], $radius, $offset);
        $imanee->setResource($canvas);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_gaussian';
    }
}
