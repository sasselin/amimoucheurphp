{if $enddate!=null && $enddate >0 }
    <script type="text/javascript">
        $(document).ready(function () {
			try {
				$('#future_date_{$id_cate}_{$idproduct}').countdown({
					until: new Date({$enddate|date_format:"%Y"}, {$enddate|date_format:"%m"}-1, {$enddate|date_format:"%d"}, {$enddate|date_format:"%H"}, {$enddate|date_format:"%M"}, {$enddate|date_format:"%S"})
				});
			}
			catch(err) {}
        });
    </script>

{/if}
