<div class="pos-logo-container">	
		<div class="container-inner">
			<div class="pos-logo">
				<div class="pos-content-logo">
					<div class="pos-logo-slide">
						{foreach from=$logos item=logo name=posLogo}
							<div class="item">
							    <a href ="{$logo.link}">
									<img src ="{$logo.image}" alt ="{l s='Logo' mod='poslogo'}" />
							    </a>
							</div>
						{/foreach}
					</div>
					<a class="prevLogo"><i class="icon-angle-left"></i></a>
					<a class="nextLogo"><i class="icon-angle-right"></i></a>
				</div>
			</div>
		</div>
	</div>
<script type="text/javascript"> 
    $(document).ready(function() {
		var owl = $(".pos-logo-slide");
		owl.owlCarousel({
		items :{$slideOptions.min_item},
		itemsDesktop : [1024,4],
		itemsDesktopSmall : [900,3], 
		itemsTablet: [600,2], 
		itemsMobile : [480,1]
		});
		 
		// Custom Navigation Events
		$(".nextLogo").click(function(){
		owl.trigger('owl.next');
		})
		$(".prevLogo").click(function(){
		owl.trigger('owl.prev');
		})     
    });
</script>
		 
</div>