{*
* 2007-2015 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}

<h3>{l s='Frequently Asked Questions' mod='gshopping'}</h3>
<div class="faq items">
	<ul id="basics" class="faq-items">
		<li class="faq-item">
			<span class="faq-trigger">{l s='For the Google Shopping module, where would I go to subscribe to a third party provider?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
				{l s='To be able to use this module fully, you need to have a Google account (such as Gmail, for example) and subscribe to the Google price comparer, called Google Merchant Center : ' mod='gshopping'}
				<a target="_blank" href='https://www.google.com/merchants/Signup'>{l s=' here.' mod='gshopping'}</a>
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='What kind of attributes are required by Google for each product in order to validate the feed?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
				{l s='Google requires a certain number of attributes for products you want to export to Google Shopping. Some of those attributes are not mandatory in PrestaShop, but if you do not fill them in, the feed won’t be exported and Google will report you an error.' mod='gshopping'}
				<p>{l s='Find below three types of required attributes for Google: ' mod='gshopping'}</p>
					<ul>
						<li>
							{l s='Product Description' mod='gshopping'}
						</li>
						<li>
							{l s='Brand *' mod='gshopping'}
						</li>
						<li>
							{l s='GTIN code (EAN 13 or UPC or USBN code) *' mod='gshopping'}
						</li>
					</ul>
				</p>
				<p>
					{l s='If you haven’t filled in those attributes for your products, please ensure to do it on the product pages before saving and exporting your feed.' mod='gshopping'}
				</p>
				<p>
					{l s=' * The two last attributes are required only in several countries (France, Germany, UK, USA and Japan)' mod='gshopping'}
				</p>
				{l s='Note: Required attributes for Google Shopping can differ according to countries and Google categories. For more information, see this' mod='gshopping'} <a target="_blank" href='https://support.google.com/merchants/answer/1344057?hl=en'>{l s=' link.' mod='gshopping'}</a>
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='How do I configure the module?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
				{l s='How do I configure the module? See the User documentation here' mod='gshopping'}
				<a target="_blank" href="{$module_dir|escape:'htmlall':'UTF-8'}{$guide_link|escape:'htmlall':'UTF-8'}" target="_blank">(C.f. documentation).</a>
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='Which are the countries in which Google is available' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
					{l s='Australia, Austria, Belgium, Brazil, Canada, Czech Republic, Denmark, France, Germany, India, Italy, Japan, Mexico, Netherlands, Norway, Poland, Russia, Spain, Sweden, Switzerland, Turkey, United Kingdom and United State, among others.' mod='gshopping'}
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='I get an error message regarding micro data. What does mean?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
					{l s='You are using a wrong option to send the data to google. Our module don\'t work with micro data, you should use the export by feed.' mod='gshopping'}
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='Where can I see the full taxonomy of the categories offered in Google?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
					{l s='See this ' mod='gshopping'} <a target="_blank" href='https://support.google.com/merchants/answer/160081?hl=en'>{l s=' link.' mod='gshopping'}</a>
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='I want to export Appareil & Accessories categories, what information about attributes I have to send?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
				<p>{l s='The attributes required are: ' mod='gshopping'}</p>
					<ul>
						<li>
							{l s='Gender (Male, Female, Unisex)' mod='gshopping'}
						</li>
						<li>
							{l s='Age group (New born, Infant, Toddler, Kids, Adult)' mod='gshopping'}
						</li>
						<li>
							{l s='Colour' mod='gshopping'}
						</li>
						<li>
							{l s='Material (not required)' mod='gshopping'}
						</li>
						<li>
							{l s='Pattern (not required)' mod='gshopping'}
						</li>
					</ul>
				</p>
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='How send a feed to Google?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
					{l s='You will find this information on the User documentation, section 4 “Insert the feed into Google Merchant”' mod='gshopping'}
			</div>
		</li>
		<li class="faq-item">
			<span class="faq-trigger">{l s='How many products and categories can export to Google with this module?' mod='gshopping'}</span>
			<span class="expand pull-right">+</span>
			<div class="faq-content">
					{l s='In order to succeed your export, up to 200,000 products per category and up to 200 categories must be exported. Beyond these limits, the proper behaviour of the module is not guaranteed' mod='gshopping'}
			</div>
		</li>
	</ul>
</div>