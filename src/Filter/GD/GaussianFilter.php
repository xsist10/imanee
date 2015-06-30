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
        // Since we don't have a nice Gaussian blur function in GD, to achieve
        // a similar effect we're going to layer multiple semi-transparent
        // copies of the image on top of itself to create our blur effect

        // Determine basic parameters for Gaussian blur
        $options = array_merge(['radius' => 2, 'sigma' => 2], $options);

        $diameter = $options['radius'] * 2;
        // 4 images per radius unit 
        $transparency = ceil(4 * 100 / $diameter);

        // Create new resource with boarders extended to account for the radius
        // we're going to blend from
        $canvas = new GDResource();
        $size = $imanee->getSize();
        $canvas->createNew($size['width'] + $diameter, $size['height'] + $diameter);

        //  We're going to start at the outter each of the radius and move
        //  inwards. We'll place an image in the 4 corners then move 1 radius
        //  inwards until we reach the center. This creates a blur with the
        //  center as the focal point
        for ($i=0; $i<$options['radius']; $i++) {

            // Lay out the image offsets
            $grid = [
                [ $i,             $i             ],
                [ $diameter - $i, $i             ],
                [ $diameter - $i, $diameter - $i ],
                [ $i,             $diameter - $i ]
            ];

            foreach ($grid as $offset) {
                // Merge out layers
                imagecopymerge(
                    $canvas->getResource(),
                    $imanee->getResource()->getResource(),
                    $offset[0],
                    $offset[1],
                    0,
                    0,
                    $size['width'],
                    $size['height'],
                    $transparency
                );
            }
        }

        // Trim off our extended boundaries
        $canvas->crop($size['width'], $size['height'], $options['radius'], $options['radius']);
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
