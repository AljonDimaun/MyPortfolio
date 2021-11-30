<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorFiltersProcess{

	const DEBUG_MAIN_QUERY = false;
	const DEBUG_FILTER = false;
	
	private static $filters = null;
	private static $arrInputFiltersCache = null;
	private static $arrFiltersAssocCache = null;
	private static $currentTermCache = null;	
	
	private static $isScriptAdded = false;
	private static $isFilesAdded = false;
	private static $isStyleAdded = false;
	private static $isAjaxCache = null;
	
	private static $showDebug = false;
	private static $originalQueryVars = null;
	
	const TYPE_TERMS = "terms";
	
	
	/**
	 * get request array
	 */
	private function getArrRequest(){
		
		$request = $_GET;
		if(!empty($_POST))
			$request = array_merge($request, $_POST);
		
		return($request);
	}
	
	/**
	 * parse base query
	 */
	private function parseBaseFilters($strBase){
		
		if(empty($strBase))
			return(null);
		
		$arrFilter = explode("~", $strBase);
		
		if(count($arrFilter) != 2)
			return(null);

		$term = $arrFilter[0];
		$value = $arrFilter[1];
			
		$arrBase = array();
		$arrBase[$term] = $value;
		
		return($arrBase);
	}
	
	
	/**
	 * parse filters string
	 */
	private function parseStrTerms($strFilters){
		
		$strFilters = trim($strFilters);
		
		$arrFilters = explode(";", $strFilters);
		
		//fill the terms
		$arrTerms = array();
		
		foreach($arrFilters as $strFilter){
			
			$arrFilter = explode("~", $strFilter);
			
			if(count($arrFilter) != 2)
				continue;
			
			$key = $arrFilter[0];
			$strValues = $arrFilter[1];
			
			$arrVales = explode(".", $strValues);
			
			$type = self::TYPE_TERMS;
			
			switch($type){
				case self::TYPE_TERMS:
					$arrTerms[$key] = $arrVales;
				break;
			}
			
		}
		
		$arrOutput = array();
		
		if(!empty($arrTerms))
			$arrOutput[self::TYPE_TERMS] = $arrTerms;
		
		return($arrOutput);
	}
	
	
	/**
	 * get filters array from input
	 */
	private function getArrInputFilters(){
		
		if(!empty(self::$arrInputFiltersCache))
			return(self::$arrInputFiltersCache);
		
		$request = $this->getArrRequest();
				
		$strTerms = UniteFunctionsUC::getVal($request, "ucterms");
				
		$arrOutput = array();
		
		//parse filters
		
		if(!empty($strTerms)){
			if(self::$showDebug == true)
				dmp("input filters found: $strTerms");
			
			$arrOutput = $this->parseStrTerms($strTerms);
		}
		
		//page
		
		$page = UniteFunctionsUC::getVal($request, "ucpage");
		$page = (int)$page;
		
		if(!empty($page))
			$arrOutput["page"] = $page;
		
		//num items
			
		$numItems = UniteFunctionsUC::getVal($request, "uccount");
		$numItems = (int)$numItems;
		
		if(!empty($numItems))
			$arrOutput["num_items"] = $numItems;
				
		self::$arrInputFiltersCache = $arrOutput;
		
		return($arrOutput);
	}
	
	
	/**
	 * get input filters in assoc mode
	 */
	private function getInputFiltersAssoc(){
		
		if(!empty(self::$arrFiltersAssocCache))
			return(self::$arrFiltersAssocCache);
		
		$arrFilters = $this->getArrInputFilters();
		
		$output = array();
		
		$terms = UniteFunctionsUC::getVal($arrFilters, "terms");
		
		if(empty($terms))
			$terms = array();
		
		foreach($terms as $taxonomy=>$arrTermSlugs){
				
			foreach($arrTermSlugs as $slug){
				
				$key = "term_{$taxonomy}_{$slug}";
				
				$output[$key] = true;
			}
			
		}
		
		self::$arrFiltersAssocCache = $output;
		
		return($output);
	}
	
	
	/**
	 * get filters arguments
	 */
	public function getRequestFilters(){
		
		if(self::$filters !== null)
			return(self::$filters);
		
		self::$filters = array();
		
		$arrInputFilters = $this->getArrInputFilters();
		
		if(empty($arrInputFilters))
			return(self::$filters);
		
		$arrTerms = UniteFunctionsUC::getVal($arrInputFilters, self::TYPE_TERMS);
		
		if(!empty($arrTerms))
			self::$filters["terms"] = $arrTerms;
		
		//collect term filters
		
		/*
		$arrFilterTerms = array();
		
		foreach($arrTerms as $taxonomy=>$arrTerms){
			
			$prefix = "";
			if($taxonomy != "category")
				$prefix = $taxonomy."--";
			
			foreach($arrTerms as $term)
				$arrFilterTerms[] = $prefix.$term;
		}
		
		//put to output
		
		if(!empty($arrFilterTerms)){
			self::$filters["category"] = $arrFilterTerms;
			self::$filters["category_relation"] = "OR";
		}
		
		*/
		
		//get the page
		
		$page = UniteFunctionsUC::getVal($arrInputFilters, "page");
		
		if(!empty($page))
			self::$filters["page"] = $page;
		
		//get num items
			
		$numItems = UniteFunctionsUC::getVal($arrInputFilters, "num_items");
		
		if(!empty($numItems))
			self::$filters["num_items"] = $numItems;
		
		
		return(self::$filters);
	}
	
	
	/**
	 * check if under ajax request
	 */
	private function isUnderAjax(){
		
		$ajaxAction = UniteFunctionsUC::getPostGetVariable("ucfrontajaxaction","",UniteFunctionsUC::SANITIZE_TEXT_FIELD);
		
		if(!empty($ajaxAction))
			return(true);
		
		return(false);
	}
	
	
	/**
	 * get fitler url from the given slugs
	 */
	private function getUrlFilter_term($term, $taxonomyName){
		
		$key = "filter-term";
		
		$taxPrefix = $taxonomyName."--";
		
		if($taxonomyName == "category"){
			$taxPrefix = "";
			$key="filter-category";
		}
				
		$slug = $term->slug;

		$value = $taxPrefix.$slug;
		
		$urlAddition = "{$key}=".urlencode($value);
				
		$urlCurrent = GlobalsUC::$current_page_url;
				
		$url = UniteFunctionsUC::addUrlParams($urlCurrent, $urlAddition);
		
		return($url);
	}
	
	/**
	 * check if the term is acrive
	 */
	private function isTermActive($term, $arrActiveFilters = null){
		
		if(empty($term))
			return(false);
		
		if($arrActiveFilters === null)
			$arrActiveFilters = $this->getRequestFilters();
		
		if(empty($arrActiveFilters))
			return(false);
		
		$taxonomy = $term->taxonomy;
		
		$selectedTermID = UniteFunctionsUC::getVal($arrActiveFilters, $taxonomy);
		
		if(empty($selectedTermID))
			return(false);
			
		if($selectedTermID === $term->term_id)
			return(true);
			
		return(false);
	}
	
	/**
	 * get current term by query vars
	 */
	private function getCurrentTermByQueryVars($queryVars){
		
		if(is_array($queryVars) == false)
			return(null);
		
		if(empty($queryVars))
			return(null);
			
		if(count($queryVars) > 1)
			return(null);
		
		$postType = null;
		if(isset($queryVars["post_type"])){
			
			$postType = $queryVars["post_type"];
			unset($queryVars["post_type"]);
		}
		
		$args = array();
		if(!empty($postType))
			$args["post_type"] = $postType;
		
		if(!empty($queryVars)){
			$taxonomy = null;
			$slug = null;
	
			foreach($queryVars as $queryTax=>$querySlug){
							
				$taxonomy = $queryTax;
				$slug = $querySlug;
			}
			
			$args = array();
			$args["taxonomy"] = $taxonomy;
			$args["slug"] = $slug;			
		}
				
		$arrTerms = get_terms($args);
		
		$isError = is_wp_error($arrTerms);
		
		if($isError == true){
			if(self::$showDebug == true){
				
				dmp("error get terms");
				dmp($args);
				dmp($arrTerms);
			}
			
			UniteFunctionsUC::throwError("cannot get the terms");
		}
			
		if(empty($arrTerms))
			return(null);
			
		$term = $arrTerms[0];
		
		return($term);
	}
	
	
	/**
	 * get current term
	 */
	private function getCurrentTerm(){
		
		if(!empty(self::$currentTermCache))
			return(self::$currentTermCache);
		
		if(is_archive() == false)
			return(null);
		
		if(!empty(self::$originalQueryVars)){
			
			$currentTerm = $this->getCurrentTermByQueryVars(self::$originalQueryVars);
		}else{
			$currentTerm = get_queried_object();
			
			
			
			if($currentTerm instanceof WP_Term == false)
				$currentTerm = null;
		}
		
		self::$currentTermCache = $currentTerm;
		
		return($currentTerm);
	}
	
	private function _______FILTER_ARGS__________(){}
	
	
	/**
	 * get offset
	 */
	private function processRequestFilters_setPaging($args, $page, $numItems){
		
		if(empty($page))	
			return(null);
		
		$perPage = UniteFunctionsUC::getVal($args, "posts_per_page");
		
		if(empty($perPage))
			return($args);
		
		$offset = null;
		$postsPerPage = null;
		
		//set posts per page and offset
		if(!empty($numItems) && $page > 1){
			
			if($page == 2)
				$offset = $perPage;
			else if($page > 2)
				$offset = $perPage+($page-2)*$numItems;
			
			$postsPerPage = $numItems;
				
		}else{	//no num items
			$offset = ($page-1)*$perPage;
		}
			
		if(!empty($offset))
			$args["offset"] = $offset;
		
		if(!empty($postsPerPage))
			$args["posts_per_page"] = $postsPerPage;
		
		return($args);
	}
	
	/**
	 * get tax query from terms array
	 */
	private function getTaxQuery($arrTax){
		
		$arrQuery = array();
		
		foreach($arrTax as $taxonomy=>$arrTerms){
			
			$item = array();
			$item["taxonomy"] = $taxonomy;
			$item["field"] = "slug";
			$item["terms"] = $arrTerms;
			
			$arrQuery[] = $item;
		}
		
		$arrQuery["relation"] = "AND";
		
		return($arrQuery);
	}
	
	
	/**
	 * process request filters
	 */
	public function processRequestFilters($args){
		
		$isUnderAjax = $this->isUnderAjax();
		
		if($isUnderAjax == false)
			return($args);
			
		$arrFilters = $this->getRequestFilters();
		
		//---- set offset and count ----
		
		$page = UniteFunctionsUC::getVal($arrFilters, "page");
		$numItems = UniteFunctionsUC::getVal($arrFilters, "num_items");
		
		if(!empty($page)){
			$args = $this->processRequestFilters_setPaging($args, $page, $numItems);
		}
		
		$arrTerms = UniteFunctionsUC::getVal($arrFilters, "terms");
		if(!empty($arrTerms)){
			
			$arrTaxQuery = $this->getTaxQuery($arrTerms);
			
			if(!empty($arrTaxQuery))
				$args["tax_query"] = $arrTaxQuery;
		}
		
		if(self::DEBUG_FILTER == true){
			dmp("debug!!!");
			dmp($args);
			dmp($arrFilters);
			exit();
		}
		
		
		return($args);
	}
	

	private function _______AJAX__________(){}
	
	/**
	 * get addon post list name
	 */
	private function getAddonPostListName($addon){
		
		$paramPostList = $addon->getParamByType(UniteCreatorDialogParam::PARAM_POSTS_LIST);
				
		$postListName = UniteFunctionsUC::getVal($paramPostList, "name");
		
		return($postListName);
	}
	
	
	/**
	 * validate if the addon ajax ready
	 * if it's have post list and has option that enable ajax
	 */
	private function validateAddonAjaxReady($addon, $arrSettingsValues){
		
		$paramPostList = $addon->getParamByType(UniteCreatorDialogParam::PARAM_POSTS_LIST);
		
		if(empty($paramPostList))
			UniteFunctionsUC::throwError("Widget not ready for ajax");
		
		$postListName = UniteFunctionsUC::getVal($paramPostList, "name");
					
		$isAjaxReady = UniteFunctionsUC::getVal($arrSettingsValues, $postListName."_isajax");
		$isAjaxReady = UniteFunctionsUC::strToBool($isAjaxReady);
		
		if($isAjaxReady == false)
			UniteFunctionsUC::throwError("The ajax is not ready for this widget");
			
		return($postListName);
	}
	
	
	/**
	 * process the html output - convert all the links, remove the query part
	 */
	private function processAjaxHtmlOutput($html){

		$currentUrl = GlobalsUC::$current_page_url;
		
		$arrUrl = parse_url($currentUrl);
		
		$query = "?".UniteFunctionsUC::getVal($arrUrl, "query");
				
		$html = str_replace($query, "", $html);
		
		$query = str_replace("&", "&#038;", $query);
		
		$html = str_replace($query, "", $html);

		return($html);
	}
	
	/**
	 * modify settings values before set to addon
	 * set pagination type to post list values
	 */
	private function modifySettingsValues($arrSettingsValues, $postListName){
		
		$paginationType = UniteFunctionsUC::getVal($arrSettingsValues, "pagination_type");
		
		if(!empty($paginationType))
			$arrSettingsValues[$postListName."_pagination_type"] = $paginationType;

		return($arrSettingsValues);			
	}
	
	/**
	 * get content element html
	 */
	private function getContentWidgetHtml($arrContent, $elementID, $isGrid = true){
		
		$arrElement = HelperProviderCoreUC_EL::getArrElementFromContent($arrContent, $elementID);
		
		if(empty($arrElement))
			UniteFunctionsUC::throwError("Elementor Widget not found");
		
		$type = UniteFunctionsUC::getVal($arrElement, "elType");
		
		if($type != "widget")
			UniteFunctionsUC::throwError("The element is not a widget");
		
		$widgetType = UniteFunctionsUC::getVal($arrElement, "widgetType");
		
		if(strpos($widgetType, "ucaddon_") === false)
			UniteFunctionsUC::throwError("Cannot output widget content");

		$arrSettingsValues = UniteFunctionsUC::getVal($arrElement, "settings");
		
		$widgetName = str_replace("ucaddon_", "", $widgetType);
		
		$addon = new UniteCreatorAddon();
		$addon->initByAlias($widgetName, GlobalsUC::ADDON_TYPE_ELEMENTOR);

		//make a check that ajax option is on in this widget
		
		if($isGrid == true){
			
			$postListName = $this->validateAddonAjaxReady($addon, $arrSettingsValues);
			
			$arrSettingsValues = $this->modifySettingsValues($arrSettingsValues, $postListName);
		}
		
		$addon->setParamsValues($arrSettingsValues);
		
		//------ get the html output
				
		//collect the debug html
		
		if($isGrid == true)
			ob_start();
		
			$objOutput = new UniteCreatorOutput();
			$objOutput->initByAddon($addon);

		if($isGrid == true){
			$htmlDebug = ob_get_contents();
			ob_end_clean();
		}
		
		
		$output = array();
		
		//get only items
		if($isGrid == true){
			$htmlGridItems = $objOutput->getHtmlItems();
			$output["html"] = $htmlGridItems;
		}
		
		//get output of the html template
		if($isGrid == false){
			
			$htmlBody = $objOutput->getHtmlOnly();
			
			$htmlBody = $this->processAjaxHtmlOutput($htmlBody);
			
			$output["html"] = $htmlBody;
		}
		
		if($isGrid == true && !empty($htmlDebug))
			$output["html_debug"] = $htmlDebug;
		
		return($output);
	}
	
	
	/**
	 * get content widgets html
	 */
	private function getContentWidgetsHTML($arrContent, $strIDs){
		
		if(empty($strIDs))
			return(null);
		
		$arrIDs = explode(",", $strIDs);
		
		$arrHTML = array();
		
		foreach($arrIDs as $elementID){
			
			$output = $this->getContentWidgetHtml($arrContent, $elementID, false);
			
			$html = UniteFunctionsUC::getVal($output, "html");
			
			$arrHTML[$elementID] = $html;
		}
		
		return($arrHTML);
	}
	
	/**
	 * get widget ajax data
	 */
	private function putWidgetGridFrontAjaxData(){
		
		//validate by response code
		
		$responseCode = http_response_code();
		
		if($responseCode != 200){
			http_response_code(200);
			UniteFunctionsUC::throwError("Request not allowed, please make sure the pagination is allowed for the ajax grid");
		}
		
		//init widget by post id and element id
		
		$layoutID = UniteFunctionsUC::getPostGetVariable("layoutid","",UniteFunctionsUC::SANITIZE_KEY);
		$elementID = UniteFunctionsUC::getPostGetVariable("elid","",UniteFunctionsUC::SANITIZE_KEY);
		
		$addElIDs = UniteFunctionsUC::getPostGetVariable("addelids","",UniteFunctionsUC::SANITIZE_TEXT_FIELD);
				
		$arrContent = HelperProviderCoreUC_EL::getElementorContentByPostID($layoutID);
		
		if(empty($arrContent))
			UniteFunctionsUC::throwError("Elementor content not found");
		
		$arrHtmlWidget = $this->getContentWidgetHtml($arrContent, $elementID);
		
		$htmlGridItems = UniteFunctionsUC::getVal($arrHtmlWidget, "html");
		$htmlDebug = UniteFunctionsUC::getVal($arrHtmlWidget, "html_debug");
		
		$addWidgetsHTML = $this->getContentWidgetsHTML($arrContent, $addElIDs);
		
		//output the html
		$outputData = array();		
		
		if(!empty($htmlDebug))
			$outputData["html_debug"] = $htmlDebug;
		
		$outputData["html_items"] = $htmlGridItems;
		
		if(!empty($addWidgetsHTML))
			$outputData["html_widgets"] = $addWidgetsHTML;
					
		HelperUC::ajaxResponseData($outputData);
		
	}
	
	
	private function _______WIDGET__________(){}
	
	
	/**
	 * include the filters js files
	 */
	private function includeJSFiles(){
		
		if(self::$isFilesAdded == true)
			return(false);
		
		$urlFiltersJS = GlobalsUC::$url_assets_libraries."filters/ue_filters.js";
		HelperUC::addScriptAbsoluteUrl($urlFiltersJS, "ue_filters");		
		
		
		self::$isFilesAdded = true;
	}
	
	/**
	 * put custom scripts
	 */
	private function putCustomJsScripts(){
		
		if(self::$isScriptAdded == true)
			return(false);
		
		self::$isScriptAdded = true;
		
		$arrData = $this->getFiltersJSData();
				
		$strData = UniteFunctionsUC::jsonEncodeForClientSide($arrData);
		
		$script = "//Unlimited Elements Filters \n";
		$script .= "var g_strFiltersData = $strData";
		
		UniteProviderFunctionsUC::printCustomScript($script);
	}
	
	/**
	 * put custom style
	 */
	private function putCustomStyle(){
		
		if(self::$isStyleAdded == true)
			return(false);
		
		self::$isStyleAdded = true;
		
		$style = "
			.uc-ajax-loading{
				opacity:0.6;
			}
		";
		
		UniteProviderFunctionsUC::printCustomStyle($style);
	}
	
	
	/**
	 * include the client side scripts
	 */
	private function includeClientSideScripts(){
		
		$this->includeJSFiles();
		
		$this->putCustomJsScripts();
		
		$this->putCustomStyle();
		
	}
	
	
	/**
	 * put terms checkboxes
	 */
	private function getHtml_termsCheckboxes($terms, $arrActiveFilters,$taxonomy){
		
		$html = "";
		foreach($terms as $term){
			
			$arrTerm = (array)$term;
			
			$termID = UniteFunctionsUC::getVal($arrTerm, "term_id");
			$name = UniteFunctionsUC::getVal($arrTerm, "name");
			$slug = UniteFunctionsUC::getVal($arrTerm, "slug");
			$count = UniteFunctionsUC::getVal($arrTerm, "count");
			
			$activeKey = "term_{$taxonomy}_{$slug}";
			
			$addAttr = "";
			
			if(isset($arrActiveFilters[$activeKey])){
				$addAttr = " checked='checked' data-active='yes'";
			}
				
			$slug = htmlspecialchars($slug);
			
			$checkboxName = "ucfilter_term__{$taxonomy}--{$slug}";
			
			$html .= "<label class='ucfilters-label-checkbox'>$name ($count)
				<input type='checkbox' class='uc-grid-filter uc-filter-checkbox' 
					name='{$checkboxName}' 
					data-type='term' 
					data-taxonomy='{$taxonomy}' 
					data-term='{$slug}'
					{$addAttr}
				 >
			</label>";
			
		}
		
		return($html);
		
	}
	
	
	/**
	 * get active archive terms
	 */
	private function getActiveArchiveTerms($taxonomy){
		
		if(is_archive() == false)
			return(null);

		$currentTerm = $this->getCurrentTerm();

		if(empty($currentTerm))
			return(null);
		
		if($currentTerm instanceof WP_Term == false)
			return(null);
		
		$termID = $currentTerm->term_id;
		
		$args = array();
		$args["taxonomy"] = $taxonomy;
		$args["parent"] = $termID;
		
		$arrTerms = get_terms($args);
		
		return($arrTerms);
	}
	
	
	/**
	 * put checkbox filters test
	 */
	public function putCheckboxFiltersTest($data){
				
		$arrActiveFilters = $this->getInputFiltersAssoc();
		
		$taxonomy = UniteFunctionsUC::getVal($data, "taxonomy", "category");
				
		//remove me
		$taxonomy = "product_cat";
				
		$terms = $this->getActiveArchiveTerms($taxonomy);
		
		if(empty($terms))
			return(null);
		
		$this->includeClientSideScripts();
		
		$html = $this->getHtml_termsCheckboxes($terms, $arrActiveFilters,$taxonomy);
		
		echo $html;
	}
	
		
	
	/**
	 * add widget variables
	 * uc_listing_addclass, uc_listing_attributes
	 */
	public function addWidgetFilterableVariables($data, $addon){
		
		$param = $addon->getParamByType(UniteCreatorDialogParam::PARAM_POSTS_LIST);
		
		$postListName = UniteFunctionsUC::getVal($param, "name");
		
		$dataPosts = UniteFunctionsUC::getVal($data, $postListName);
		
		//check if ajax related
		$isAjax = UniteFunctionsUC::getVal($dataPosts, $postListName."_isajax");
		$isAjax = UniteFunctionsUC::strToBool($isAjax);
		
		if($isAjax == false)
			return($data);
				
		if(empty($param))
			return($data);
		
		//check if ajax
		$strAttributes = "";
		
		if($isAjax == true)
			$strAttributes .= " data-ajax='true' ";
		
		$this->includeClientSideScripts();
		
		$data["uc_filtering_attributes"] = $strAttributes;
		$data["uc_filtering_addclass"] = " uc-filterable-grid";
		
		
		return($data);
	}
	
	
	/**
	 * get filters attributes
	 */
	private function getFiltersJSData(){
		
		$urlBase = UniteFunctionsUC::getBaseUrl(GlobalsUC::$current_page_url);
		
		$arrData = array();
		$arrData["urlbase"] = $urlBase;
		$arrData["urlajax"] = GlobalsUC::$url_ajax_full;
		$arrData["querybase"] = self::$originalQueryVars;

		
		return($arrData);
	}
	
	private function _______EDITOR_ARGUMENTS__________(){}
	
	/**
	 * get editor filter arguments
	 */
	public function addEditorFilterArguments($data, $isInitAfter){
		
		$arguments = "";
		$style = "";
		
		if($isInitAfter == true){
			$arguments = " data-initafter=\"true\"";
			$style = " xvisibility:hidden;background-color:green;";
		}
		
		$data["filter_arguments"] = $arguments;
		$data["filter_style"] = $style;
		
		return($data);
	}
	
	
	private function _______ARCHIVE_QUERY__________(){}
	
	
	/**
	 * modify post query
	 */
	public function checkModifyMainQuery($query){
		
		if(is_single())
			return(false);
		
		self::$originalQueryVars = $query->query_vars;

		$arrFilters = $this->getRequestFilters();
		
		if(empty($arrFilters))
			return(true);
				
		$args = UniteFunctionsWPUC::getPostsArgs($arrFilters, true);
		
		if(empty($args))
			return(false);
		
		$query->query_vars = array_merge($query->query_vars, $args);
		
	}
	
	
	/**
	 * show the main query debug
	 */
	private function showMainQueryDebug(){
		
		
		global $wp_query;
		
		$args = $wp_query->query_vars;
				
		$argsForDebug = UniteFunctionsWPUC::cleanQueryArgsForDebug($args);
		
		dmp("MAIN QUERY DEBUG");
		
		dmp($argsForDebug);
		
	}
	
	/**
	 * is ajax request
	 */
	public function isFrontAjaxRequest(){
		
		if(self::$isAjaxCache !== null)
			return(self::$isAjaxCache);
		
		$frontAjaxAction = UniteFunctionsUC::getPostGetVariable("ucfrontajaxaction","",UniteFunctionsUC::SANITIZE_KEY);
		
		if($frontAjaxAction == "getfiltersdata"){
			self::$isAjaxCache = true;
			return(true);
		}
		
		self::$isAjaxCache = false;
		
		return(false);
	}
	
	/**
	 * test the request filter
	 */
	public function operateAjaxResponse(){
		
		if(self::DEBUG_MAIN_QUERY == true){
			$this->showMainQueryDebug();
			exit();
		}
		
		$frontAjaxAction = UniteFunctionsUC::getPostGetVariable("ucfrontajaxaction","",UniteFunctionsUC::SANITIZE_KEY);
		
		if(empty($frontAjaxAction))
			return(false);
			
		try{
			
			switch($frontAjaxAction){
				case "getfiltersdata":
					$this->putWidgetGridFrontAjaxData();
				break;
			}
		
		}catch(Exception $e){
			
			$message = $e->getMessage();
			
			HelperUC::ajaxResponseError($message);
			
		}
		
	}
	
	
	/**
	 * init wordpress front filters
	 */
	public function initWPFrontFilters(){
				
		if(is_admin() == true)
			return(false);
		
		add_action("wp", array($this, "operateAjaxResponse"));
		
		//add_action("parse_request", array($this, "checkModifyMainQuery"));
				
	}
	
	
}