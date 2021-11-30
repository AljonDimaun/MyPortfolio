
function UEDynamicFilters(){
	
	var g_objFilters, g_objGrid, g_filtersData, g_urlBase;
	var g_urlAjax, g_lastGridAjaxCall, g_cache = {};
	
	var g_types = {
		PAGINATION:"pagination",
		LOADMORE:"loadmore",
		TERMS_LIST:"terms_list",
		CHECKBOX: "checkbox"
	};
	
	var g_vars = {
		CLASS_DIV_DEBUG:"uc-div-ajax-debug",
		CLASS_GRID:"uc-filterable-grid",		
		handleTrashold:null,
		DEBUG_AJAX_OPTIONS: false
	};
	
	var g_options = {
		is_cache_enabled:true,
		ajax_reload: false,
		widget_name: null
	};
	
	
	/**
	 * console log some string
	 */
	function trace(str){
		console.log(str);
	}
	
	function ________GENERAL_______________(){}
	
		
	
	/**
	 * add url param
	 */
	function addUrlParam(url, param, value){
		
		if(url.indexOf("?") == -1)
			url += "?";
		else
			url += "&";
		
		if(typeof value == "undefined")
			url += param;
		else	
			url += param + "=" + value;
		
		return(url);
	}
	
	
	/**
	 * get object property
	 */
	function getVal(obj, name, defaultValue){
		
		if(!defaultValue)
			var defaultValue = "";
		
		var val = "";
		
		if(!obj || typeof obj != "object")
			val = defaultValue;
		else if(obj.hasOwnProperty(name) == false){
			val = defaultValue;
		}else{
			val = obj[name];			
		}
		
		return(val);
	}
	
	/**
	 * turn string value ("true", "false") to string 
	 */
	function strToBool(str){
		
		switch(typeof str){
			case "boolean":
				return(str);
			break;
			case "undefined":
				return(false);
			break;
			case "number":
				if(str == 0)
					return(false);
				else 
					return(true);
			break;
			case "string":
				str = str.toLowerCase();
						
				if(str == "true" || str == "1")
					return(true);
				else
					return(false);
				
			break;
		}
		
		return(false);
	};
	
	/**
	 * get offsets distance
	 */
	function getOffsetsDistance(offset1, offset2){
	  
	  var dx = offset2.left-offset1.left;
	  var dy = offset2.top-offset1.top;
	  
	  return Math.sqrt(dx*dx+dy*dy); 
	}
	
	
	/**
	 * get closest object by offset
	 */
	function getClosestByOffset(objParents, objElement, isVertical){
		
		var objClosest = null;
		var minDiff = 1000000;
		
		var elementOffset = objElement.offset();
		
		jQuery.each(objParents, function(index, parent){
			
			var objParent = jQuery(parent);
				
			var distance = 0;
			var parentOffset = objParent.offset();
			
			if(isVertical == true){
				
				var offsetY = elementOffset.top;
				var parentY = parentOffset.top;
				
				//get bottom of the parent
				if(parentY < offsetY)
					parentY += objParent.height();
				
				var distance = Math.abs(offsetY - parentY);
				
			}else{
				
				var parentOffset = objParent.offset();

				var distance = getOffsetsDistance(parentOffset, elementOffset);
			}
					
			if(distance < minDiff){
				minDiff = distance;
				objClosest = objParent;
			}
			
		});
				
		return(objClosest);
	}
	
	/**
	 * get all grids
	 */
	function getAllGrids(){
		
		var objGrids = jQuery("."+ g_vars.CLASS_GRID);
						
		return(objGrids);
	}
	
	/**
	 * get closest grid to some object
	 */
	function getClosestGrid(objSource){
		
		//in case there is only one grid - return it
		if(g_objGrid)
			return(g_objGrid);
		
		//in case there are nothing:
		var objGrids = getAllGrids();
		
		if(objGrids.length == 0)
			return(null);
		
		//get from current section
		var objSection = objSource.parents("section");
		
		var objGrid = objSection.find("."+ g_vars.CLASS_GRID);
				
		if(objGrid.length == 1)
			return(objGrid);
				
		//get closest by offset
		var type = getFilterType(objSource);
		
		switch(type){
			case g_types.LOADMORE:
			case g_types.PAGINATION:
				var objSingleGrid = getClosestByOffset(objGrid, objSource, true);
				
				return(objSingleGrid);
				
			break;
		}
		
		
		//get by next or prev section
		
		var objPrevSection = objSection;
		var objNextSection = objSection;
		
		//get from previous section
		do{
			objPrevSection = objPrevSection.prev();
			objNextSection = objNextSection.next();
			
			objGrid = objPrevSection.find("."+ g_vars.CLASS_GRID);
			if(objGrid.length == 1)
				return(objGrid);
			
			objGrid = objNextSection.find("."+ g_vars.CLASS_GRID);
			if(objGrid.length == 1)
				return(objGrid);
			
		}while(objNextSection.length != 0 && objNextSection != 0);
		
		//return first grid in the list
		
		var objFirstGrid = jQuery(objGrids[0]);
		return(objFirstGrid);
	}
	
	
	/**
	 * add filter object to grid
	 */
	function bindFilterToGrid(objGrid, objFilter){
		
		var arrFilters = objGrid.data("filters");
		var objTypes = objGrid.data("filter_types");
		
		if(!arrFilters)
			arrFilters = [];
		
		if(!objTypes)
			objTypes = {};
		
		var type = getFilterType(objFilter);
		
		//validate double types
		
		if(objTypes.hasOwnProperty(type)){
			
			switch(type){
				case g_types.LOADMORE:
					
					trace("Double filter not allowed");					
					trace("existing Filters:");
					trace(arrFilters);
					
					trace("Second Filter");
					trace(objFilter);
					
					trace("Grid:");
					trace(objGrid);
										
					showElementError(objFilter, "Double load more button for one grid not allowed")
					return(false);
				break;
			}
			
		}
		
		objTypes[type] = true;
				
		arrFilters.push(objFilter);
		
		//add init after filters
		var isInitAfter = objFilter.data("initafter");
		
		if(isInitAfter === true){
			
			var arrFiltersInitAfter = objGrid.data("filters_init_after");
			
			if(!arrFiltersInitAfter)
				arrFiltersInitAfter = [];
			
			arrFiltersInitAfter.push(objFilter);
			
			objGrid.data("filters_init_after", arrFiltersInitAfter);
		}
		
		objGrid.data("filters", arrFilters);
		objGrid.data("filter_types", objTypes);
		
	}
	
	/**
	 * 
	 * get element widget id from parent wrapper
	 */
	function getElementWidgetID(objElement){
		
		if(!objElement || objElement.length == 0)
			throw new Error("Element not found");
		
		//get widget id
		
		var objWidget = objElement.parents(".elementor-widget");
		
		if(objWidget.langth == 0)
			throw new Error("Element parent not found");
		
		var widgetID = objWidget.data("id");
		
		if(!widgetID)
			throw new Error("widget id not found");
		
		return(widgetID);
	}
	
	
	/**
	 * get element layout data
	 */
	function getElementLayoutData(objElement){
		
		if(!objElement || objElement.length == 0)
			throw new Error("Element not found");
		
		//get widget id
		
		var objWidget = objElement.parents(".elementor-widget");
		
		if(objWidget.langth == 0)
			throw new Error("Element parent not found");
		
		var widgetID = objWidget.data("id");
		
		if(!widgetID)
			throw new Error("widget id not found");
			
		//get layout id
		var objLayout = objWidget.parents(".elementor");
		
		if(objLayout.length == 0)
			throw new Error("layout not found");
		
		var layoutID = objLayout.data("elementor-id");
		
		var output = {};
		
		output["widgetid"] = widgetID;
		output["layoutid"] = layoutID;
		
		return(output);
	}
	
	/**
	 * show element error above it
	 */
	function showElementError(objElement, error){
		
		var objParent = objElement.parent();
		
		var objError = objParent.find(".uc-filers-error-message");
		if(objError.length == 0){
			objParent.append("<div class='uc-filers-error-message' style='color:red;position:absolute;top:-24px;left:0px;'></div>");
			var objError = objParent.find(".uc-filers-error-message");
			objParent.css("border","1px solid red !important");
		}
		
		objError.append(error);
		
	}
	
	function ________FILTERS_______________(){}
	
	
	/**
	 * get filter type
	 */
	function getFilterType(objFilter){
		
		if(objFilter.hasClass("uc-filter-pagination"))
			return(g_types.PAGINATION);
				
		if(objFilter.hasClass("uc-filter-load-more"))
			return(g_types.LOADMORE);
		
		var filterType = objFilter.data("filtertype")
		
		if(filterType)
			return(filterType);
		
		trace(objFilter);
		throw new Error("wrong filter type");
		
		return(null);
	}
	
	
	
	function ________PAGINATION_FILTER______(){}
	
	
	/**
	 * get pagination selected url or null if is current
	 */
	function getPaginationSelectedUrl(objPagination){
		
		var objCurrentLink = objPagination.find("a.current");
		
		if(objCurrentLink.length == 0)
			return(null);
		
		var url = objCurrentLink.attr("href");
		
		if(!url)
			return(null);
		
		return(url);
	}
	
	
	
	/**
	 * on ajax pagination click
	 */
	function onAjaxPaginationLinkClick(event){
		
		var objLink = jQuery(this);
		
		var objPagination = objLink.parents(".uc-filter-pagination");
		
		var objLinkCurrent = objPagination.find(".current");
		
		objLinkCurrent.removeClass("current");
		
		objLink.addClass("current");
				
		var objGrid = objPagination.data("grid");
		
		if(!objGrid || objGrid.length == 0)
			throw new Error("Grid not found!");
		
		//run the ajax, prevent default
				
		refreshAjaxGrid(objGrid);
		
		event.preventDefault();
		return(false);
	}

	function ________LOAD_MORE_______________(){}
	
	
	/**
	 * get current load more page
	 */
	function getLoadMoreUrlData(objFilter){
		
		var objData = objFilter.find(".uc-filter-load-more__data");
				
		var nextPage = objData.data("nextpage");
		if(!nextPage)
			nextPage = null;
		
		var numItems = objFilter.data("numitems");
		
		if(!numItems)
			numItems = null;
		
		var data = {};
		data.page = nextPage;
		data.numItems = numItems;
		
		return(data);
	}
	
	
	/**
	 * init load more filter
	 */
	function initLoadMoreFilter(objLoadMore){
		
		var objData = objFilter.find(".uc-filter-load-more__data");
		
		var isMore = objData.data("more");
		if(isMore !== true)
			return(false);
	
		//check if nessesary
		objLoadMore.addClass("uc-loadmore-active");
	}
	
	
	/**
	 * do the load more operation
	 */
	function onLoadMoreClick(){
				
		var objLink = jQuery(this);
		
		var objLoadMore = objLink.parents(".uc-filter-load-more");
		
		var objData = objLoadMore.find(".uc-filter-load-more__data");
		
		var isMore = objData.data("more");
		
		if(isMore == false)
			return(false);

		var objGrid = objLoadMore.data("grid");
		
		if(!objGrid || objGrid.length == 0)
			throw new Error("Grid not found!");
		
		//run the ajax, prevent default
				
		refreshAjaxGrid(objGrid, true);
		
	}
	
	function ________CHECKBOX_______________(){}
	
	/**
	 * init checkbox filter - uncheck all checkboxes if checked by cache
	 */
	function initCheckboxFilter(){
		
		var objChecks = jQuery(".ue-filter-checkbox__check");
		
		objChecks.each(function(index, check){
			
			var objCheck = jQuery(check);
			
			var hasAttr = objCheck.attr("checked");
			
			if(!hasAttr)
				objCheck.prop("checked",false);				
			
		});
				
	}
	
	
	/**
	 * add checkbox terms to terms list
	 */
	function addCheckboxTerms(objFilter, arrTerms){
		
		var objChecks = jQuery(".ue-filter-checkbox__check");
		
		jQuery.each(objChecks, function(index, check){
			
			var objCheck = jQuery(check);
			
			var isChecked = objCheck.is(":checked");
			
			if(isChecked == false)
				return(true);
			
			var slug = objCheck.data("slug");
			var taxonomy = objCheck.data("taxonomy");
			
			var objTerm = {
				"slug": slug,
				"taxonomy": taxonomy
			};
			
			arrTerms.push(objTerm);
			
		});
		
		return(arrTerms);
	}
	
	
	/**
	 * on checkbox change
	 */
	function onCheckboxChange(){
		
		var objLink = jQuery(this);
		var objCheckboxFilter = objLink.parents(".uc-grid-filter");
		setNoRefreshFilter(objCheckboxFilter);		
		
		//refresh grid
		var objGrid = objCheckboxFilter.data("grid");
		
		refreshAjaxGrid(objGrid);
		
	}
	
	
	function ________TERMS_LIST_______________(){}
	
	
	/**
	 * on terms list click
	 */
	function onTermsLinkClick(event){
		
		var className = "uc-selected";
		
		event.preventDefault();
		
		var objLink = jQuery(this);
		
		var objTermsFilter = objLink.parents(".uc-grid-filter");
		
		//set not refresh next iteration, because of the clicked
		setNoRefreshFilter(objTermsFilter);		
		
		var objActiveLinks = objLink.siblings("."+className).not(objLink);
		
		objActiveLinks.removeClass(className);
		objLink.addClass(className);
		
		var objGrid = objTermsFilter.data("grid");
		
		if(!objGrid || objGrid.length == 0)
			throw new Error("Grid not found");
		
		//refresh grid		
		refreshAjaxGrid(objGrid);
		
	}
		
	
	/**
	 * get terms list term id
	 */
	function getTermsListSelectedTerm(objFilter){
		
		if(!objFilter)
			return(null);
		
		var objSelected = objFilter.find(".uc-selected");
		
		if(objSelected.length == 0)
			return(null);
		
		var slug = objSelected.data("slug");
		var taxonomy = objSelected.data("taxonomy");
		
		var objTerm = {
			"slug": slug,
			"taxonomy": taxonomy
		};
		
		return(objTerm);
	}
	
	
	function ________DATA_______________(){}
	
	 	
	/**
	 * consolidate filters data
	 */
	function consolidateFiltersData(arrData){
		
		if(arrData.length == 0)
			return([]);
		
		//consolidate by taxonomies
		
		var objTax = {};
		
		jQuery.each(arrData, function(index, item){
			
			switch(item.type){
				case "term":
					
					var taxonomy = item.taxonomy;
					var term = item.term;
					
					if(objTax.hasOwnProperty(taxonomy) == false)
						objTax[taxonomy] = [];
					
					objTax[taxonomy].push(term);
					
				break;
				default:
					throw new Error("consolidateFiltersData error: wrong type: "+item.type);
				break;
			}
			
		});
		
		var arrConsolidated = {};
		arrConsolidated["terms"] = objTax;
		
		return(arrConsolidated);
	}
	
	/**
	 * build terms query
	 * ucterms=product_cat~shoes.dress;cat~123.43;
	 */
	function buildTermsQuery(arrTerms){
		
		var query = "";
				
		//break by taxonomy
		var arrTax = {};
		jQuery.each(arrTerms, function(index, objTerm){
			
			var taxonomy = objTerm["taxonomy"];
			var slug = objTerm["slug"];
			
			var objTax = getVal(arrTax, taxonomy);
			if(!objTax)
				objTax = {};
			
			objTax[slug] = true;
			arrTax[taxonomy] = objTax;
			
		});
		
		//combine the query
		
		if(!arrTax)
			return(null);
		
		jQuery.each(arrTax,function(taxonomy,objSlugs){
			
			var strSlugs = "";
			
			for (var slug in objSlugs){
				
				if(strSlugs)
					strSlugs += ".";
				
				strSlugs += slug;
			}
			
			var strTax = taxonomy+"~"+strSlugs;
						
			if(query)
				query += ";";
			
			query += strTax;
			
		});
		
		
		return(query);
	}
	
	
	function ________AJAX_CACHE_________(){}

	/**
	 * get ajax url
	 */
	function getAjaxCacheKeyFromUrl(ajaxUrl){
		
		var key = ajaxUrl;
		
		key = key.replace(g_urlAjax, "");
		key = key.replace(g_urlBase, "");
		
		//replace special signs
		key = replaceAll(key, "/","");
		key = replaceAll(key, "?","_");
		key = replaceAll(key, "&","_");
		key = replaceAll(key, "=","_");
		
		return(key);
	}
	
	/**
	 * get ajax cache key
	 */
	function getAjaxCacheKey(ajaxUrl, action, objData){
		
	    if(g_options.is_cache_enabled == false)
	    	return(false);
	    
	    //cache only by url meanwhile
	    
	    if(jQuery.isEmptyObject(objData) == false)
	    	return(false);
	    
	    if(action)
	    	return(false);
	    
	    var cacheKey = getAjaxCacheKeyFromUrl(ajaxUrl);
	    
	    if(!cacheKey)
	    	return(false);
	    
	    return(cacheKey);
	}
	
	
	/**
	 * cache ajax response
	 */
	function cacheAjaxResponse(ajaxUrl, action, objData, response){
		
	    var cacheKey = getAjaxCacheKey(ajaxUrl, action, objData);
	    
	    if(!cacheKey)
	    	return(false);
	    
	    //some precoutions for overload
	    if(g_cache.length > 100)
	    	return(false);
	    
	    g_cache[cacheKey] = response;
	    
	}
	
		
	function ________AJAX_RESPONSE_______________(){}

	/**
	 * replace the grid debug
	 */
	function operateAjax_setHtmlDebug(response, objGrid){
				
		//replace the debug
		var htmlDebug = getVal(response, "html_debug");
				
		if(!htmlDebug)
			return(false);
		
		var gridParent = objGrid.parent();
				
		var objDebug = objGrid.siblings(".uc-debug-query-wrapper");
		
		if(objDebug.length == 0)
			return(false);
		
		objDebug.replaceWith(htmlDebug);
	}
	
	
	/**
	 * set html grid from ajax response
	 */
	function operateAjax_setHtmlGrid(response, objGrid, isLoadMore){
		
		if(objGrid.length == 0)
			return(false);
		
		objItemsWrapper = getGridItemsWrapper(objGrid);
		
		var htmlItems = getVal(response, "html_items");
		
		if(isLoadMore === true){
			
			objItemsWrapper.append(htmlItems);
			
		}else{
			objItemsWrapper.html(htmlItems);
		}
			
		operateAjax_setHtmlDebug(response, objGrid);
		
	}
	
	
	/**
	 * replace filters html
	 */
	function operateAjax_setHtmlWidgets(response, objFilters){
		
		if(!objFilters)
			return(false);
		
		if(objFilters.length == 0)
			return(false);
		
		var objHtmlWidgets = getVal(response, "html_widgets");
		
		if(!objHtmlWidgets)
			return(false);
		
		if(objHtmlWidgets.length == 0)
			return(false);
		
		jQuery.each(objFilters, function(index, objFilter){
			
			var widgetID = getElementWidgetID(objFilter);
			
			if(!widgetID)
				return(true);
			
			var html = getVal(objHtmlWidgets, widgetID);
			
			var objHtml = jQuery(html);
			
			var htmlInner = objHtml.html();
			
			objFilter.html(htmlInner);
			
			objFilter.trigger("uc_ajax_reloaded");
			
		});
		
	}
	
	/**
	 * scroll to grid top
	 */
	function scrollToGridTop(objGrid){
		
		var gapTop = 150;
		
		var gridOffset = objGrid.offset().top;
		
		var gridTop = gridOffset - gapTop;
		
		if(gridTop < 0)
			gridTop = 0;
		
		//check if the grid top is visible
		
		var currentPos = jQuery(window).scrollTop();
		
		if(currentPos <= gridOffset)
			return(false);
		
		window.scrollTo({ top: gridTop, behavior: 'smooth' });
		
	}
	
	
	/**
	 * operate the response
	 */
	function operateAjaxRefreshResponse(response, objGrid, objFilters, isLoadMore){
		
		operateAjax_setHtmlGrid(response, objGrid, isLoadMore);
		
		operateAjax_setHtmlWidgets(response, objFilters);
		
		objGrid.trigger("uc_ajax_refreshed");
		
		//scroll to top
		if(isLoadMore == false){
			
			setTimeout(function(){
				
				scrollToGridTop(objGrid);
				
			},200);
			
		}
				
	}
	
	
	/**
	 * replace all occurances
	 */
	function replaceAll(text, from, to){
		
		return text.split(from).join(to);		
	};
	
	
	
	
	/**
	 * get response from ajax cache
	 */
	function getResponseFromAjaxCache(ajaxUrl, action, objData){
	
	    var cacheKey = getAjaxCacheKey(ajaxUrl, action, objData);
	    
	    if(!cacheKey)
	    	return(false);
		
	    var response = getVal(g_cache, cacheKey);
	    
	    return(response);
	}
	
	
	function ________AJAX_______________(){}
	
	/**
	 * set this filter not to refresh next time
	 */
	function setNoRefreshFilter(objFilter){
		
		objFilter.data("uc_norefresh",true);
		
	}
	
	/**
	 * show ajax error, should be something visible
	 */
	function showAjaxError(message){
		
		alert(message);
		
	}
	
	/**
	 * get the debug object
	 */
	function getDebugObject(){
		
		var objGrid = g_lastGridAjaxCall;
		
		if(!objGrid)
			return(null);
		
		var objDebug = objGrid.find("."+g_vars.CLASS_DIV_DEBUG);
		
		if(objDebug.length)
			return(objDebug);
		
		//insert if not exists
		
		objGrid.after("<div class='"+g_vars.CLASS_DIV_DEBUG+"' style='padding:10px;display:none;background-color:#D8FCC6'></div>");
		
		var objDebug = jQuery("body").find("."+g_vars.CLASS_DIV_DEBUG);
		
		return(objDebug);
	}
	
	
	/**
	 * show ajax debug
	 */
	function showAjaxDebug(str){
		
		str = jQuery.trim(str);
		
		if(!str || str.length == 0)
			return(false);
		
		var objStr = jQuery(str);
		
		if(objStr.find("header").length || objStr.find("body").length){
			str = "Wrong ajax response!";
		}
		
		var objDebug = getDebugObject();
		
		if(!objDebug || objDebug.length == 0){
			
			alert(str);
			
			throw new Error("debug not found");
		}
		
		objDebug.show();
		objDebug.html(str);
		
	}
	
	
	/**
	 * small ajax request
	 */
	function ajaxRequest(ajaxUrl, action, objData, onSuccess){
		
		if(!objData)
			var objData = {};
		
		if(typeof objData != "object")
			throw new Error("wrong ajax param");
		
		//check response from cache
		var responseFromCache = getResponseFromAjaxCache(ajaxUrl, action, objData);
		
		if(responseFromCache){
			
			//simulate ajax request
			setTimeout(function(){
				onSuccess(responseFromCache);
			}, 300);
			
			return(false);
		}		
		
		var ajaxData = {};
		ajaxData["action"] = "unlimitedelements_ajax_action";
		ajaxData["client_action"] = action;
		
		var ajaxtype = "get";
		
		if(objData){
			ajaxData["data"] = objData;
			ajaxtype = "post";
		}
		
		var ajaxOptions = {
				type:ajaxtype,
				url:ajaxUrl,
				success:function(response){
					
					if(!response){
						showAjaxError("Empty ajax response!");
						return(false);					
					}
										
					if(typeof response != "object"){
						
						try{
							
							response = jQuery.parseJSON(response);
							
						}catch(e){
							
							showAjaxDebug(response);
							
							showAjaxError("Ajax Error!!! not ajax response");
							return(false);
						}
					}
					
					if(response == -1){
						showAjaxError("ajax error!!!");
						return(false);
					}
					
					if(response == 0){
						showAjaxError("ajax error, action: <b>"+action+"</b> not found");
						return(false);
					}
					
					if(response.success == undefined){
						showAjaxError("The 'success' param is a must!");
						return(false);
					}
					
					
					if(response.success == false){
						showAjaxError(response.message);
						return(false);
					}
					
					cacheAjaxResponse(ajaxUrl, action, objData, response);
					
					if(typeof onSuccess == "function"){
										
						onSuccess(response);
					}
					
				},
				error:function(jqXHR, textStatus, errorThrown){
										
					switch(textStatus){
						case "parsererror":
						case "error":
							
							//showAjaxError("parse error");
							
							showAjaxDebug(jqXHR.responseText);
							
						break;
					}
				}
		}
		
		if(ajaxtype == "post"){
			ajaxOptions.dataType = 'json';
			ajaxOptions.data = ajaxData
		}
		
		jQuery.ajax(ajaxOptions);
		
	}
	
	
	
	/**
	 * get grid items wrapper
	 */
	function getGridItemsWrapper(objGrid){
		
		if(objGrid.hasClass("uc-items-wrapper"))
			return(objGrid);
		
		var objItemsWrapper = objGrid.find(".uc-items-wrapper");
		
		if(objItemsWrapper.length == 0)
			throw new Error("Missing items wrapper - with class: uc-items-wrapper");
		
		return(objItemsWrapper);
	}
	
	
	/**
	 * set ajax loader
	 */
	function showAjaxLoader(objElement){
				
		objElement.addClass("uc-ajax-loading");		
	}
	
	/**
	 * hide ajax loader
	 */
	function hideAjaxLoader(objElement){
		
		objElement.removeClass("uc-ajax-loading");		
	}
	
	
	/**
	 * show multiple ajax loader
	 */
	function showMultipleAjaxLoaders(objElements, isShow){
		
		if(!objElements)
			return(false);
		
		if(objElements.length == 0)
			return(false);
		
		jQuery.each(objElements,function(index, objElement){
			
			if(isShow == true){
								
				showAjaxLoader(objElement);
			}
			else
				hideAjaxLoader(objElement);
		});
		
	}
	
		
	/**
	 * refresh ajax grid
	 */
	function refreshAjaxGrid(objGrid, isLoadMore){
		
		//get all grid filters
		var objFilters = objGrid.data("filters");
		
		if(!objFilters)
			return(false);
		
		if(objFilters.length == 0)
			return(false);
		
		var objAjaxOptions = getGridAjaxOptions(objFilters, objGrid);
		
		if(!objAjaxOptions)
			return(false);
				
		var ajaxUrl = objAjaxOptions["ajax_url"];
		
		if(g_vars.DEBUG_AJAX_OPTIONS == true){
			
			trace("DEBUG AJAX OPTIONS");
			trace(objAjaxOptions);
			return(false);
		}
		
		if(isLoadMore !== true)
			showAjaxLoader(objGrid);
		
		var objFiltersToReload = objFilters.filter(function(objFilter){
			
			return objFilter.hasClass("uc-ajax-refresh-soon");
		});
		
		showMultipleAjaxLoaders(objFiltersToReload, true);
		
		g_lastGridAjaxCall = objGrid;
						
		ajaxRequest(ajaxUrl,null,null, function(response){
			
			if(isLoadMore !== true)
				hideAjaxLoader(objGrid);
			
			showMultipleAjaxLoaders(objFilters, false);
			
			operateAjaxRefreshResponse(response, objGrid, objFilters, isLoadMore);
			
		});
	}
	
	
	function ________RUN_______________(){}
		
	
	/**
	 * get grid ajax options
	 */
	function getGridAjaxOptions(objFilters, objGrid){
				
		if(!objFilters)
			return(false);
		
		var urlAjax = g_urlBase;
		
		var strRefreshIDs = "";
		
		var urlFiltersAddition = "";
		var page = null;
		var numItems = null;
		var arrTerms = [];
		
		//get ajax options
		jQuery.each(objFilters, function(index, objFilter){
			
			var type = getFilterType(objFilter);
			
			switch(type){
				case g_types.PAGINATION:
					
					var urlPagination = getPaginationSelectedUrl(objFilter);
					
					if(urlPagination)
						urlAjax = urlPagination;
				break;
				case g_types.LOADMORE:
										
					var loadMoreData = getLoadMoreUrlData(objFilter);
					page = loadMoreData.page;
					numItems = loadMoreData.numItems;
										
					if(!page)
						urlAjax = null;
				break;
				case g_types.TERMS_LIST:
					
					var objTerm = getTermsListSelectedTerm(objFilter);
					
					if(objTerm)
						arrTerms.push(objTerm);
					
				break;
				case g_types.CHECKBOX:
					
					arrTerms = addCheckboxTerms(objFilter, arrTerms);
											
				break;
				default:
					throw new Error("Unknown filter type: "+type);
				break;
			}
						
			//add widget id of the filter to refresh
			
			var isNoRefresh = objFilter.data("uc_norefresh");
			objFilter.data("uc_norefresh",false);
			
			if(isNoRefresh !== true){
				var filterWidgetID = getElementWidgetID(objFilter);
				
				if(strRefreshIDs)
					strRefreshIDs += ",";
				
				strRefreshIDs += filterWidgetID;
				
				objFilter.addClass("uc-ajax-refresh-soon");
				
			}
			
			
		});		//end filters iteration
		
		if(urlAjax == null)
			return(null);
		
		var dataLayout = getElementLayoutData(objGrid);
		
		var widgetID = dataLayout["widgetid"];
		var layoutID = dataLayout["layoutid"];
		
		var urlAddition = "ucfrontajaxaction=getfiltersdata&layoutid="+layoutID+"&elid="+widgetID;
		
		urlAjax = addUrlParam(urlAjax, urlAddition);
				
		if(page)
			urlAjax += "&ucpage="+page;
		
		if(numItems)
			urlAjax += "&uccount="+numItems;
		
		if(arrTerms.length){
			var strTerms = buildTermsQuery(arrTerms);
			if(strTerms)
				urlAjax += "&ucterms="+strTerms;
		}
			
		//add refresh ids
		if(strRefreshIDs)
			urlAjax += "&addelids="+strRefreshIDs;
		
		var output = {};
		output["ajax_url"] = urlAjax;
		
		return(output);
	}
	
	
	
	function ________INIT_______________(){}
	
		
	/**
	 * init listing object
	 */
	function initGridObject(){
		
		//init the listing
		g_objGrid = jQuery("."+ g_vars.CLASS_GRID);
		
		if(g_objGrid.length == 0){
			g_objGrid = null;
			return(false);
		}
		
		//set only available grid
		if(g_objGrid.length > 1){
			g_objGrid = null;
		}
								
	}
	
		
	
	/**
	 * init the globals
	 */
	function initGlobals(){
		
		if(typeof g_strFiltersData != "undefined"){
			g_filtersData = JSON.parse(g_strFiltersData);
		}
		
		if(jQuery.isEmptyObject(g_filtersData)){
			
			trace("filters error - filters data not found");
			return(false);
		}
		
		g_urlBase = getVal(g_filtersData, "urlbase");
		g_urlAjax = getVal(g_filtersData, "urlajax");
		
		if(!g_urlBase){
			trace("ue filters error - base url not inited");
			return(false);
		}

		if(!g_urlAjax){
			trace("ue filters error - ajax url not inited");
			return(false);
		}
		
		return(true);
	}
	
	
	
	/**
	 * init filter and bing to grid
	 */
	function initFilter(objFilter){
		
		var objGrid = getClosestGrid(objFilter);
				
		if(!objGrid)
			return(null);
		
		var isAjax = objGrid.data("ajax");
		
		if(isAjax == false)
			return(false);
		
		//bind grid to filter
		objFilter.data("grid", objGrid);
		
		//bind filter to grid
		bindFilterToGrid(objGrid, objFilter);
		
	}
	
	
	/**
	 * init filter events by types
	 */
	function initFilterEventsByTypes(arrTypes, objFilters){
		
		if(!arrTypes || arrTypes.length == 0)
			return(false);
		
		//init the events
		var objParent = objFilters.parents(".elementor");
		
		for(var type in arrTypes){
						
			switch(type){
				case g_types.PAGINATION:
					objParent.on("click",".uc-filter-pagination a", onAjaxPaginationLinkClick);			
				break;
				case g_types.LOADMORE:
					
					//load more
					objParent.on("click",".uc-filter-load-more__link", onLoadMoreClick);
				break;
				case g_types.TERMS_LIST:
					
					objParent.on("click",".ue_taxonomy a.ue_taxonomy_item", onTermsLinkClick);
					
				break;
				case g_types.CHECKBOX:
					
					initCheckboxFilter();
					
					objParent.on("change",".ue-filter-checkbox__check", onCheckboxChange);
										
				break;
				default:
					trace("init by type - unrecognized type: "+type);
				break;
			}
		}
				
	}
		
	/**
	 * init pagination filter
	 */
	function initFilters(){
		
		var objFilters = jQuery(".uc-grid-filter,.uc-filter-pagination");
		
		if(objFilters.length == 0)
			return(false);
		
		var arrTypes = {};
		
		jQuery.each(objFilters, function(index, filter){
			
			var objFilter = jQuery(filter);
			
			initFilter(objFilter);
			
			var type = getFilterType(objFilter);
						
			arrTypes[type] = true;
			
		});
		
		initFilterEventsByTypes(arrTypes, objFilters);
		
	}
	
	/**
	 * ajax init grid filters
	 */
	function ajaxInitGridFilters(objGrid){
		
		trace("ajax init");
		
		
	}
	
	/**
	 * check and call ajax init filters
	 */
	function ajaxInitFilters(){
		
		var objGrids = getAllGrids();
		
		if(objGrids.length == 0)
			return(false);
		
		jQuery.each(objGrids, function(index, grid){
			
			var objGrid = jQuery(grid);
			
			var objInitFilters = objGrid.data("ajax_init_filters");
			
			/*
				trace("check for init after");
				trace(objInitFilters);
			*/
			
			//ajaxInitGridFilters(objGrid);
			
		});
				
		
	}
	
	
	/**
	 * init
	 */
	function init(){
				
		var success = initGlobals();
		
		if(success == false)
			return(false);
		
		//init the grid object
		initGridObject();
		
		initFilters();
		
		//ajaxInitFilters();
				
	}
	
	/**
	 * is element in viewport
	 */
	this.isElementInViewport = function(objElement) {
		  
		  var elementTop = objElement.offset().top;
		  var elementBottom = elementTop + objElement.outerHeight();

		  var viewportTop = jQuery(window).scrollTop();
		  var viewportBottom = viewportTop + jQuery(window).height();

		  return (elementBottom > viewportTop && elementTop < viewportBottom);
	}
	
	
	/**
	 * init the class
	 */
	function construct(){
		
		if(!jQuery){
			trace("Filters not loaded, jQuery not loaded");
			return(false);
		}
		
		jQuery("document").ready(init);
		
	}
	
	construct();
}

g_ucDynamicFilters = new UEDynamicFilters();

