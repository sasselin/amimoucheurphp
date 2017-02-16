{if $page_name == 'index'}
    <div class="pos-slideshow-container">
	<div class="flexslider ma-nivoslider">
        <div class="pos-loading"></div>
            <div id="pos-slideshow-home" class="slides">
                {$count=0}
                {foreach from=$slides key=key item=slide}
                    <img style ="display:none" src="{$slide.image}" data-thumb="{$slide.image}"  alt="" title="#htmlcaption{$slide.id_pos_slideshow}"  />
                {/foreach}
            </div>
            {if $slideOptions.show_caption != 0}
                {foreach from=$slides key=key item=slide}
                    <div id="htmlcaption{$slide.id_pos_slideshow}" class="pos-slideshow-caption nivo-html-caption nivo-caption">
                            <div class="pos-slideshow-des">
                                {$slide.description}
                            </div>
                            {if $slide.link}
                            <div class="pos-slideshow-readmore">
                                <a href="{$slide.link}" title="{l s=('Read more') mod='posslideshow'}">{l s=('Shop now') mod= 'posslideshow'}</a>	
                            </div>
                            {/if}
                    </div>
                 {/foreach}
             {/if}
        </div>
    </div>

 <script type="text/javascript">
    $(window).load(function() {
        $('#pos-slideshow-home').nivoSlider({
			effect: '{if $slideOptions.animation_type != ''}{$slideOptions.animation_type}{else}random{/if}',
			slices: 15,
			boxCols: 8,
			boxRows: 4,
			animSpeed: '{if $slideOptions.animation_speed != ''}600{else}600{/if}',
			pauseTime: '{if $slideOptions.pause_time != ''}7000{else}7000{/if}',
			startSlide: {if $slideOptions.start_slide != ''}{$slideOptions.start_slide}{else}0{/if},
			directionNav: {if $slideOptions.show_arrow != 0}{$slideOptions.show_arrow}{else}false{/if},
			controlNav: {if $slideOptions.show_navigation != 0}{$slideOptions.show_navigation}{else}false{/if},
			controlNavThumbs: false,
			pauseOnHover: true,
			manualAdvance: false,
			prevText: 'Prev',
			nextText: 'Next',
                        afterLoad: function(){
                         $('.pos-loading').css("display","none");
                        },     
                        beforeChange: function(){ 
                            $('.pos-slideshow-title, .pos-slideshow-des').css("left","-100%" );
                            $('.pos-slideshow-readmore').css("left","-100%"); 
                        }, 
                        afterChange: function(){ 
                            $('.pos-slideshow-title, .pos-slideshow-des, .pos-slideshow-readmore').css("left","100px") 
                        }
 		});
    });
    </script>
{/if}