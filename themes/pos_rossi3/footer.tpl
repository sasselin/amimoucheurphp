{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !isset($content_only) || !$content_only}
					</div><!-- #center_column -->
					{if isset($right_column_size) && !empty($right_column_size)}
						<div id="right_column" class="col-xs-12 col-sm-4 col-md-{$right_column_size|intval} column">{$HOOK_RIGHT_COLUMN}</div>
					{/if}
					
				</div><!-- #columns -->
			</div><!-- .columns-container -->
			<div class="container">
				<div class="col-xs-12">
					{hook h="brandSlider"}
				</div>
			</div>
			<!-- Footer -->
			<div id="footer" class="footer-container">
				<div class="pos-footer-top">
					<div class="container">
						<div class="footer-top col-xs-12">
						    {hook h = "blockFooter1"}
						</div>	
					</div>
				</div>
				<div class="pos-footer-center">
					<div class="container">
					    <div class="footer-center">
						{hook h = "blockFooter2"}
					    </div>
					</div>
				</div>
				<div class="pos-footer-bottom">
					<div class="container">
						<div class="footer-bottom col-xs-12">
						    {hook h = "blockFooter3"}
						</div>
					</div>
				</div>
			</div><!-- #footer -->
		</div><!-- #page -->
{/if}
{include file="$tpl_dir./global.tpl"}
<div class="back-top"><a href= "#" class="mypresta_scrollup hidden-phone"></a></div>
	</body>
</html>