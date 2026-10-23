<?php
/**
* pagination.class.php file defines methods to calculate a pagination with page and group
*/
class BT_Pagination
{
	/**
	 * @var array $aParams : define array
	 */
	protected $aParams = array('group' => false);
	
	/**
	 * @var array $aPagination : define array
	 */
	public $aPagination = array();

	/**
	 * @var int $iStart : use to determinate begin and end value in each mode - don't have a same value with 'page' mode and 'group' mode
	 */
	protected  $iStart = 0;

	/**
	 * @var int $iEnd : use to determinate begin and end value in each mode - don't have a same value with 'page' mode and 'group' mode
	 */
	protected  $iEnd = 1;

	/**
	 * __construct()
	 *
	 * @param array $aParams
	 */
	public function __construct($aParams = null)
	{
		// set param
		if (!empty($aParams)) {
			$this->set($aParams);
		}
	}

	
	/**
	 * set() method define init parameters of class
	 * @param array $aParams
	 */
	public function set(array $aParams)
	{
		foreach ($aParams as $sParam => $mValue) { 
			if (array_key_exists($sParam, $this->aParams)) {
				$this->aParams[$sParam] = $mValue;
			}
		}
	}
	
	/**
	 * run() method process pagination
	 *
	 * @param array $aParams
	 * @return array
	 */
	public function run(array $aParams)
	{
		if (!array_key_exists('total' , $aParams)
			|| !array_key_exists('perPage' , $aParams)
		) {
			// throw exception
			throw new Exception('There is not all valid keys : "total" & "perPage"');
		}
		else {
			// flush array if necessary
			$this->aPagination = array();
			// get pagination
			$this->_get('page' , $aParams['total'], $aParams['perPage']);

			// use case - group
			if ($this->aParams['group'] === true) {
				if (!array_key_exists('perGroup' , $aParams)) {
					// throw exception
					throw new Exception('Param "perGroup" do not exists');
				}
				else {
					$this->iStart 	= 1;
					$this->iEnd 	= 0;
					// get pagination
					$this->_get('group' , count($this->aPagination['page']), $aParams['perGroup']);
				}
			}
			// use case - empty group
			if ($this->aParams['group'] === false
				|| !isset($this->aPagination['group'])
				|| (isset($this->aPagination['group'])
				&& empty($this->aPagination))
			) {
				// return
				return $this->aPagination['page'];
			}
			// use case - page & group
			else {
				// return
				return $this->aPagination;
			}
		}
	}
	
	/**
	 * _get() process pagination
	 *
	 * @param string $sType
	 * @param int $iTotal
	 * @param int $iPerContent
	 */
	private function _get($sType , $iTotal , $iPerContent)
	{
		// case - no pagination or no group
		if ($iTotal < $iPerContent) {
			$this->aPagination[$sType][1]['begin'] 	= $this->iStart;
			$this->aPagination[$sType][1]['end'] 	= $iTotal - $this->iEnd;
			$this->aPagination[$sType][1]['nb'] 	= $iTotal;
		}
		// case idem 
		elseif ($iTotal == $iPerContent) {
			$this->aPagination[$sType][1]['begin'] 	= $this->iStart;
			$this->aPagination[$sType][1]['end'] 	= $iPerContent - $this->iEnd;
			$this->aPagination[$sType][1]['nb'] 	= $iPerContent;
		}
		// case pagination
		else {
			// nb pages
			$iNbPage = floor($iTotal / $iPerContent);
			
			// set param of each page
			for ($i = 1; $i <= $iNbPage; ++$i) {
				// first 
				if ($i == 1) {
					$this->aPagination[$sType][$i]['begin'] 	= $this->iStart;
					$this->aPagination[$sType][$i]['end'] 		= $iPerContent - $this->iEnd;
				}
				else {
					$this->aPagination[$sType][$i]['begin'] 	= $this->aPagination[$sType][$i-1]['end'] + 1;
					$this->aPagination[$sType][$i]['end'] 		= $this->aPagination[$sType][$i]['begin'] + ($iPerContent - 1);
				}
				$this->aPagination[$sType][$i]['nb'] = $iPerContent;
			}
			// calculate last page
			$iDelta = $iTotal - ($iNbPage * $iPerContent);

			// only if > 0
			if ($iDelta > 0) {
				$this->aPagination[$sType][$iNbPage+1]['begin'] = $this->aPagination[$sType][$iNbPage]['end'] + 1;
				$this->aPagination[$sType][$iNbPage+1]['end'] 	= $this->aPagination[$sType][$iNbPage+1]['begin'] + ($iDelta - 1);
				$this->aPagination[$sType][$iNbPage+1]['nb'] 	= $iDelta;
			}
		}
	}

	/**
	 * create() method returns singleton
	 *
	 * @return obj
	 */
	public static function create()
	{
		static $oPagination;

		if( null === $oPagination) {
			$oPagination = new BT_Pagination();
		}
		return $oPagination;
	}
}