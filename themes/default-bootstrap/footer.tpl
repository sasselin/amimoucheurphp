{*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !isset($content_only) || !$content_only}
					</div><!-- #center_column -->
					{if isset($right_column_size) && !empty($right_column_size)}
						<div id="right_column" class="col-xs-12 col-sm-{$right_column_size|intval} column">{$HOOK_RIGHT_COLUMN}</div>
					{/if}
					</div><!-- .row -->
				</div><!-- #columns -->
			</div><!-- .columns-container -->
			{if isset($HOOK_FOOTER)}
				<!-- Footer -->
				<div class="footer-container">
					<footer id="footer"  class="container">
						<div class="row">
							{$HOOK_FOOTER}
							<section class="footer-block col-xs-12 col-sm-2">
								<h4>
									<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" title="{l s='Manage my customer account'}" rel="nofollow">{l s='My account'}</a>
								</h4>
								<div class="block_content toggle-footer">
									<ul class="bullet">
										<li><a href="{$link->getPageLink('history', true)|escape:'html':'UTF-8'}" title="{l s='My orders'}" rel="nofollow">{l s='My orders'}</a></li>
										<li><a href="{$link->getPageLink('addresses', true)|escape:'html':'UTF-8'}" title="{l s='My addresses'}" rel="nofollow">{l s='My addresses'}</a></li>
										<li><a href="{$link->getPageLink('identity', true)|escape:'html':'UTF-8'}" title="{l s='Manage my personal information'}" rel="nofollow">{l s='My personal info'}</a></li>
            				{if $is_logged}<li><a href="{$link->getPageLink('index')}?mylogout" title="{l s='Sign out'}" rel="nofollow">{l s='Sign out'}</a></li>{/if}
									</ul>
								</div>
							</section>
							<section id="block_contact_infos" class="footer-block col-xs-12 col-sm-4">
								<div>
        					<h4>L'Ami du Moucheur Inc.</h4>
									<ul class="toggle-footer">
			            	<li><i class="icon-map-marker"></i>7390 Rue Notre Dame O, Trois-Rivières, QC G9B 1L8</li>
			            	<li><i class="icon-phone"></i>{l s='Call us now'}:<span> 1 800 567-8584</span></li>
			            	<li><i class="icon-phone"></i>{l s='France'}: <span>08 00 90 36 68</span></li>
          					<li><i class="icon-envelope-alt"></i>{l s='Email'}:
          						<span>{mailto address='info@amimoucheur.com'|escape:'html':'UTF-8' encode="hex"}</span>
          					</li>
        					</ul>
    						</div>
							</section>
							<section id="block_store_hours" class="footer-block col-xs-12 col-sm-4">
								<div>
									<h4>{l s='Store hours'}</h4>
									<ul class="toggle-footer bullet">
										<li>{l s='Monday'}:  9:00 - 17:30</li>
										<li>{l s='Tuesday'}:  9:00 - 17:30</li>
										<li>{l s='Wednesday'}:  9:00 - 17:30</li>
										<li>{l s='Thursday'}:  9:00 - 21:00</li>
										<li>{l s='Friday'}:  9:00 - 21:00</li>
										<li>{l s='Saturday'}:  9:00 - 17:00</li>
										<li>{l s='Sunday'}:  10:00 - 16:00</li>
									</ul>
								</div>
							</section>
						</div>
					</footer>
				</div><!-- #footer -->
			{/if}
		</div><!-- #page -->
{/if}
{include file="$tpl_dir./global.tpl"}
	</body>
</html>
