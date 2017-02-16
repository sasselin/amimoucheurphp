<?php

class AdminPosLogoController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = 'pos_logo';
		$this->className = 'Logo';
		//$this->lang = true;
		$this->bootstrap = true;
		$this->deleted = false;
		$this->colorOnBackground = false;
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
                Shop::addTableAssociation($this->table, array('type' => 'shop'));
		$this->context = Context::getContext();
                
                $this->fieldImageSettings = array(
 			'name' => 'image',
 			'dir' => 'blocklogo'
 		);
                $this->imageType = "jpg";
		
		parent::__construct();
	}
        
        public function renderList() {
            
            $this->addRowAction('edit');
            $this->addRowAction('delete');
            $this->bulk_actions = array(
                'delete' => array(
                    'text' => $this->l('Delete selected'),
                    'confirm' => $this->l('Delete selected items?')
                )
            );

            $this->fields_list = array(
                'id_pos_logo' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'width' => 25
                ),
                  'title' => array(
                    'title' => $this->l('Title'),
                    'width' => 90,
                ),
                
                'description' => array(
                    'title' => $this->l('Desscription'),
                    'width' => '300',
                 ),
                  'porder' => array(
                    'title' => $this->l('Order'),
                    'width' => 10,
                ),
            );
            
            $this->fields_list['image'] = array(
                'title' => $this->l('Image'),
                'width' => 70,
                "image" => $this->fieldImageSettings["dir"]
            );
//            

            $lists = parent::renderList();
            parent::initToolbar();

            return $lists;
    }
    
    
    public function renderForm() {
        
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Slideshow'),
                'image' => '../img/admin/cog.gif'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title:'),
                    'name' => 'title',
                    'size' => 40
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Link:'),
                    'name' => 'link',
                    'size' => 40
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Image:'),
                    'name' => 'image',
                    'desc' => $this->l('Upload  a banner from your computer.')
                ),
              array(
                'type' => 'textarea',
                'label' => $this->l('Description'),
                'name' => 'description',
                'autoload_rte' => TRUE,
                'lang' => false,
                'required' => TRUE,
                'rows' => 5,
                'cols' => 40,
                'hint' => $this->l('Invalid characters:') . ' <>;=#{}'
               ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Order:'),
                    'name' => 'porder',
                    'size' => 40,
                    'require' => false
                ),
            ),
             'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );
                 if (Shop::isFeatureActive())
                $this->fields_form['input'][] = array(
                        'type' => 'shop',
                        'label' => $this->l('Shop association:'),
                        'name' => 'checkBoxShopAsso',
                );

        if (!($obj = $this->loadObject(true)))
            return;


        return parent::renderForm();
    }
    
    

}
