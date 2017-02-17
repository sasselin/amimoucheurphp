<?php
if (!defined('_PS_VERSION_'))
    exit;
class HomeSliderOverride extends HomeSlider
{
    protected function _prepareHook()
    {
        if (!$this->isCached('homeslider.tpl', $this->getCacheId()))
        {
            $slides = $this->getSlides(true);
            if (is_array($slides))
                foreach ($slides as &$slide)
                {
                    $slide['sizes'] = @getimagesize((dirname(__FILE__).DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$slide['image']));
                    $slide['target_blank'] = false;
                    if (isset($slide['sizes'][3]) && $slide['sizes'][3])
                        $slide['size'] = $slide['sizes'][3];
                    if (preg_match("/^https?:\/\//", $slide['url']))
                        $slide['target_blank'] = true;
                }

            if (!$slides)
                return false;

            $this->smarty->assign(array('homeslider_slides' => $slides));
        }

        return true;
    }
}
