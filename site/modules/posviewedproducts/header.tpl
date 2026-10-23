<script>
	$(document).ready(function() {
		var owl = $("#pos-viewed-products");
		owl.owlCarousel({
		items :{$PRODUCTS_VIEWED_ITEM},
		itemsDesktop : [1200,1],
		itemsDesktopSmall : [999,1],
		itemsTablet: [767,2],
		itemsMobile : [480,1] 
		});
		// Custom Navigation Events
		$(".nextViewed").click(function(){
			owl.trigger('owl.next');
		})
		$(".prevViewed").click(function(){
			owl.trigger('owl.prev');
		})
	});
</script>