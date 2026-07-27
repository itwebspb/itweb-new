var esolRRModuleName = 'esol.redirector';
var esolRRModuleFilePrefix = 'esol_redirector';

var EList = {
	Init: function()
	{
		var obj = this;
		this.InitLines();
		
		$('.kda-ie-tbl input[type=checkbox][name^="SETTINGS[CHECK_ALL]"]').bind('change', function(){
			var inputs = $(this).closest('tbody').find('input[type=checkbox]').not(this);
			if(this.checked)
			{
				inputs.attr('checked', true);
			}
			else
			{
				inputs.attr('checked', false);
			}
		});
		
		/*Bug fix with excess jquery*/
		var anySelect = $('select:eq(0)');
		if(typeof anySelect.chosen!='function')
		{
			var jQuerySrc = $('script[src*="/bitrix/js/main/jquery/"]').attr('src');
			if(jQuerySrc)
			{
				$.getScript(jQuerySrc, function(){
					$.getScript('/bitrix/js/'+esolRRModuleName+'/chosen/chosen.jquery.min.js');
				});
			}
		}
		/*/Bug fix with excess jquery*/		
		
		this.SetFieldValues();
		
		$('.kda-ie-tbl:not(.empty) div.set').bind('scroll', function(){
			$('#kda_select_chosen').remove();
			$(this).prev('.set_scroll').scrollLeft($(this).scrollLeft());
		});
		$('.kda-ie-tbl:not(.empty) div.set_scroll').bind('scroll', function(){
			$('#kda_select_chosen').remove();
			$(this).next('.set').scrollLeft($(this).scrollLeft());
		});
		$(window).bind('resize', function(){
			EList.SetWidthList();
			$('.kda-ie-tbl tr.settings .set_scroll').removeClass('fixed');
			$('.kda-ie-tbl tr.settings .set_scroll_static').remove();
		});
		BX.addCustomEvent("onAdminMenuResize", function(json){
			$(window).trigger('resize');
		});
		$(window).trigger('resize');
		
		$(window).bind('scroll', function(){
			var windowHeight = $(window).height();
			var scrollTop = $(window).scrollTop();
			$('.kda-ie-tbl tr.settings:visible').each(function(){
				var height = $(this).height();
				if(height <= windowHeight) return;
				var top = $(this).offset().top;
				var scrollDiv = $('.set_scroll', this);
				if(scrollTop > top && scrollTop < top + height - windowHeight)
				{
					if(!scrollDiv.hasClass('fixed'))
					{
						scrollDiv.before('<div class="set_scroll_static" style="height: '+(scrollDiv.height())+'px"></div>');
						scrollDiv.addClass('fixed');
					}
				}
				else if(scrollDiv.hasClass('fixed'))
				{
					$('.set_scroll_static', this).remove();
					scrollDiv.removeClass('fixed');
				}
			});
		});
		
		var sectionSelect = $('.kda-ie-tbl table.additional select');
		if(typeof sectionSelect.chosen == 'function') sectionSelect.chosen({search_contains: true});
	},
	
	InitLines: function(list)
	{
		var obj = this;
		$('.kda-ie-tbl .list input[name^="SETTINGS[IMPORT_LINE]"]').click(function(e){
			if(typeof obj.lastChb != 'object') obj.lastChb = {};
			var arKeys = this.name.substr(0, this.name.length - 1).split('][');
			var chbKey = arKeys.pop();
			var sheetKey = arKeys.pop();
			if(e.shiftKey && obj.lastChb[sheetKey] && obj.lastChb[sheetKey].checked==this.checked)
			{
				var arKeys2 = obj.lastChb[sheetKey].name.substr(0, obj.lastChb[sheetKey].name.length - 1).split('][');
				var chbKey2 = arKeys2.pop();
				var kFrom = Math.min(chbKey, chbKey2);
				var kTo = Math.max(chbKey, chbKey2);
				for(var i=kFrom+1; i<kTo; i++)
				{
					$('.kda-ie-tbl .list input[name="SETTINGS[IMPORT_LINE]['+sheetKey+']['+i+']"]').attr('checked', this.checked);
				}
			}
			obj.lastChb[sheetKey] = this;
		});
	},
	
	SetFieldValues: function(gParent, rewriteAutoinsert)
	{
		if(!gParent) gParent = $('.kda-ie-tbl');
		$('select[name^="FIELDS_LIST["]', gParent).each(function(){
			var pSelect = this;
			var parent = $(pSelect).closest('tr');
			var arVals = [];
			var arValParents = [];
			for(var i=0; i<pSelect.options.length; i++)
			{
				arVals[pSelect.options.item(i).value] = pSelect.options.item(i).text;
				arValParents[pSelect.options.item(i).value] = pSelect.options.item(i).parentNode.getAttribute('label');
			}
			
			$('input[name^="SETTINGS[FIELDS_LIST]"]', parent).each(function(index){
				var input = this;
				var inputShow = EList.GetShowInputFromValInput(input);
				inputShow.setAttribute('placeholder', arVals['']);
				
				if(!input.value || !arVals[input.value])
				{
					input.value = '';
					EList.SetShowFieldVal(inputShow, '');
					return;
				}
				EList.SetShowFieldVal(inputShow, arVals[input.value]);
			});
			
			EList.OnFieldFocus($('span.fieldval', parent));
		});
	},
	
	GetShowInputNameFromValInputName: function(name)
	{
		return name.replace(/SETTINGS\[FIELDS_LIST\]\[([\d_]*)\]\[([\d_]*)\]/, 'field-list-show-$1-$2');
	},
	
	GetShowInputFromValInput: function(input)
	{
		return $('#'+this.GetShowInputNameFromValInputName(input.name))[0];
	},
	
	GetValInputFromShowInput: function(input)
	{
		return  $(input).closest('td').find('input[name="'+input.id.replace(/field-list-show-([\d_]*)-([\d_]*)$/, 'SETTINGS[FIELDS_LIST][$1][$2]')+'"]')[0];
	},
	
	SetShowFieldVal: function(input, val)
	{
		input = $(input);
		var jsInput = input[0];
		var placeholder = jsInput.getAttribute('placeholder');
		if(val.length > 0 && val!=placeholder)
		{
			jsInput.innerHTML = val;
			input.removeClass('fieldval_empty');
		}
		else
		{
			jsInput.innerHTML = placeholder;
			input.addClass('fieldval_empty');
		}
		jsInput.title = val;
	},
	
	OnFieldFocus: function(objInput)
	{
		$(objInput).unbind('click').bind('click', function(){
			var input = this;
			var parent = $(input).closest('tr');
			var pSelect = parent.find('select[name^="FIELDS_LIST["]');
			var inputVal = EList.GetValInputFromShowInput(input);
			var select = $(pSelect).clone();
			var options = select[0].options;
			for(var i=0; i<options.length; i++)
			{
				if(inputVal.value==options.item(i).value) options.item(i).selected = true;
			}
			
			var chosenId = 'kda_select_chosen';
			$('#'+chosenId).remove();
			var offset = $(input).offset();
			var div = $('<div></div>');
			div.attr('id', chosenId);
			div.css({
				position: 'absolute',
				left: offset.left,
				top: offset.top,
				width: $(input).width() + 1
			});
			div.append(select);
			$('body').append(div);
			
			if(typeof select.chosen == 'function') select.chosen({search_contains: true});
			select.bind('change', function(){
				var option = options.item(select[0].selectedIndex);
				if(option.value)
				{
					EList.SetShowFieldVal(input, option.text);
					inputVal.value = option.value;
				}
				else
				{
					EList.SetShowFieldVal(input, '');
					inputVal.value = '';
				}
				if(typeof select.chosen == 'function') select.chosen('destroy');
				$('#'+chosenId).remove();
			});
			
			$('body').one('click', function(e){
				e.stopPropagation();
				return false;
			});
			var chosenDiv = select.next('.chosen-container')[0];
			$('a:eq(0)', chosenDiv).trigger('mousedown');
			
			var lastClassName = chosenDiv.className;
			var interval = setInterval( function() {   
				   var className = chosenDiv.className;
					if (className !== lastClassName) {
						select.trigger('change');
						lastClassName = className;
						clearInterval(interval);
					}
				},30);
		});
	},
	
	SetWidthList: function()
	{
		$('.kda-ie-tbl:not(.empty) div.set').each(function(){
			var div = $(this);
			div.css('width', 0);
			div.prev('.set_scroll').css('width', 0);
			var timer = setInterval(function(){
				var width = div.parent().width();
				if(width > 0)
				{
					div.css('width', width);
					div.prev('.set_scroll').css('width', width).find('>div').css('width', div.find('>table.list').width());
					clearInterval(timer);
				}
			}, 100);
			setTimeout(function(){clearInterval(timer);}, 3000);
		});
	},
	
	AddUploadField: function(link)
	{
		var parent = $(link).closest('.kda-ie-field-select-btns');
		var div = parent.prev('div').clone();
		var input = $('input[name^="SETTINGS[FIELDS_LIST]"]', div)[0];
		var inputShow = $('span.fieldval', div)[0];
		var a = $('a.field_settings', div)[0];
		//var inputExtra = $('input', a)[0];
		$('.field_insert', div).remove();
		
		var sname = input.name;
		var index = sname.substr(0, sname.length-1).split('][').pop();
		var arIndex = index.split('_');
		if(arIndex.length==1) arIndex[1] = 1;
		else arIndex[1] = parseInt(arIndex[1]) + 1;
		
		input.name = input.name.replace(/\[[\d_]+\]$/, '['+arIndex.join('_')+']');
		inputShow.id = this.GetShowInputNameFromValInputName(input.name);
		if(arIndex[1] > 1) a.id = a.id.replace(/\_\d+_\d+$/, '_'+arIndex.join('_'));
		else a.id = a.id.replace(/\_\d+$/, '_'+arIndex.join('_'));
		$(a).addClass('inactive');
		/*inputExtra.name = inputExtra.name.replace(/\[[\d_]+\]$/, '['+arIndex.join('_')+']');
		inputExtra.value = '';*/
		
		div.insertBefore(parent);
		EList.OnFieldFocus(inputShow);
	},
	
	DeleteUploadField: function(link)
	{
		var parent = $(link).closest('div');
		parent.remove();
	}/*,
	
	ShowFieldSettings: function(btn, name, val)
	{
		if(!name || !val)
		{
			var input = $(btn).prevAll('input[name^="SETTINGS[FIELDS_LIST]"]');
			val = input.val();
			name = input[0].name;
		}
		var input2 = $(btn).closest('div').find('span.fieldval');
		var input2Val = input2.html();
		if(input2Val==input2.attr('placeholder')) input2Val = '';
		var ptable = $(btn).closest('.kda-ie-tbl');
		var form = $(btn).closest('form')[0];
		var countCols = $(btn).closest('tr').find('.kda-ie-field-select').length;
		
		var dialogParams = {
			'title':BX.message("KDA_IE_POPUP_FIELD_SETTINGS_TITLE") + (input2Val ? ' "'+input2Val+'"' : ''),
			'content_url':'/bitrix/admin/'+esolRRModuleFilePrefix+'_field_settings.php?field='+val+'&field_name='+name+'&IBLOCK_ID='+ptable.attr('data-iblock-id')+'&PROFILE_ID='+form.PROFILE_ID.value+'&count_cols='+countCols,
			'width':'900',
			'height':'400',
			'resizable':true
		};
		if($('input', btn).length > 0)
		{
			dialogParams['content_url'] += '&return_data=1';
			dialogParams['content_post'] = {'POSTEXTRA': $('input', btn).val()};
		}
		var dialog = new BX.CAdminDialog(dialogParams);
			
		dialog.SetButtons([
			dialog.btnCancel,
			new BX.CWindowButton(
			{
				title: BX.message('JS_CORE_WINDOW_SAVE'),
				id: 'savebtn',
				name: 'savebtn',
				className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
				action: function () {
					this.disableUntilError();
					this.parentWindow.PostParameters();
				}
			})
		]);
			
		BX.addCustomEvent(dialog, 'onWindowRegister', function(){
			$('input[type=checkbox]', this.DIV).each(function(){
				BX.adminFormTools.modifyCheckbox(this);
			});
			ESettings.BindConversionEvents();
		});
			
		dialog.Show();
	}*/
}

var EImport = {
	params: {},

	Init: function(post, params)
	{
		BX.scrollToNode($('#resblock .adm-info-message')[0]);
		this.wait = BX.showWait();
		this.post = post;
		if(typeof params == 'object') this.params = params;
		this.SendData();
		this.pid = post.PROFILE_ID;
		this.idleCounter = 0;
		this.errorStatus = false;
		var obj = this;
		setTimeout(function(){obj.SetTimeout();}, 3000);
	},
	
	SetTimeout: function()
	{
		if($('#progressbar').hasClass('end')) return;
		var obj = this;
		if(this.timer) clearTimeout(this.timer);
		this.timer = setTimeout(function(){obj.GetStatus();}, 2000);
	},

	GetStatus: function()
	{
		var obj = this;
		$.ajax({
			type: "GET",
			url: '/upload/tmp/'+esolRRModuleName+'/'+this.pid+'.txt?hash='+(new Date()).getTime(),
			success: function(data){
				var finish = false;
				if(data && data.substr(0, 1)=='{' && data.substr(data.length-1)=='}')
				{
					try {
						eval('var result = '+data+';');
					} catch (err) {
						var result = false;
					}
				}
				else
				{
					var result = false;
				}
				
				if(typeof result == 'object')
				{
					if(result.action!='finish')
					{
						obj.UpdateStatus(result);
					}
					else
					{
						obj.UpdateStatus(result, true);
						var finish = true;
					}
				}
				if(!finish) obj.SetTimeout();
			},
			error: function(){
				obj.SetTimeout();
			},
			timeout: 5000
		});
	},
	
	UpdateStatus: function(result, end)
	{
		if($('#progressbar').hasClass('end')) return;
		if(end && this.timer) clearTimeout(this.timer);
		
		if(typeof result == 'object')
		{
			if(end && (parseInt(result.total_read_line) < parseInt(result.total_file_line)))
			{
				result.total_read_line = result.total_file_line;
			}
			
			$('#total_line').html(result.total_line);
			$('#correct_line').html(result.correct_line);
			$('#error_line').html(result.error_line);
			$('#element_added_line').html(result.element_added_line);
			$('#element_updated_line').html(result.element_updated_line);
			$('#element_removed_line').html(result.element_removed_line);
			
			var span = $('#progressbar .presult span');
			if(result.curstep && span.attr('data-'+result.curstep))
			{
				span.html(span.attr('data-'+result.curstep));
			}
			if(end)
			{
				span.css('visibility', 'hidden');
				$('#progressbar .presult').removeClass('load');
				$('#progressbar').addClass('end');
			}
			var percent = Math.round((result.total_read_line / result.total_file_line) * 100);
			if(percent >= 100 || isNaN(percent))
			{
				if(end) percent = 100;
				else percent = 99;
			}
			$('#progressbar .presult b').html(percent+'%');
			$('#progressbar .pline').css('width', percent+'%');
			
			var statLink = document.getElementById('kda_ie_stat_profile_link');
			if(statLink && result.loggerExecId)
			{
				statLink.href = statLink.href.replace(/find_exec_id=(&|$)/, 'find_exec_id='+result.loggerExecId);
			}
			
			if(this.tmpparams && this.tmpparams.total_read_line==result.total_read_line)
			{
				this.idleCounter++;
			}
			else
			{
				this.idleCounter = 0;
			}
			this.tmpparams = result;
		}
	},
	
	SendData: function()
	{
		var post = this.post;
		post.ACTION = 'DO_IMPORT';
		post.stepparams = this.params;
		var obj = this;
		
		$.ajax({
			type: "POST",
			url: window.location.href,
			data: post,
			success: function(data){
				obj.errorStatus = false;
				obj.OnLoad(data);
			},
			error: function(data){
				if(data && data.responseText && data.responseText.substr(0, 1)=='{' && data.responseText.substr(data.responseText.length-1)=='}')
				{
					obj.errorStatus = false;
					obj.OnLoad(data.responseText);
					return;
				}
				obj.errorStatus = true;
				$('#block_error_import').show();
				var timeBlock = document.getElementById('kda_ie_auto_continue_time');
				if(timeBlock)
				{
					timeBlock.innerHTML = '';
					obj.TimeoutOnAutoConinue();
				}
			},
			timeout: (post.STEPS_TIME ? ((Math.min(3600, post.STEPS_TIME) + 120) * 1000) : 180000)
		});
	},
	
	TimeoutOnAutoConinue: function()
	{
		var obj = this;
		var timeBlock = document.getElementById('kda_ie_auto_continue_time');
		var time = timeBlock.innerHTML;
		if(time.length==0)
		{
			timeBlock.innerHTML = 30;
		}
		else
		{
			time = parseInt(time) - 1;
			timeBlock.innerHTML = time;
			if(time < 1)
			{
				$.ajax({
					type: "POST",
					url: window.location.href,
					data: {'MODE': 'AJAX', 'PROCCESS_PROFILE_ID': obj.pid, 'ACTION': 'GET_PROCESS_PARAMS'},
					success: function(data){
						if(data && data.substr(0, 1)=='{' && data.substr(data.length-1)=='}')
						{
							try {
								eval('var params = '+data+';');
							} catch (err) {
								var params = false;
							}
							if(typeof params == 'object')
							{
								obj.params = params;
							}
						}
						$('#block_error_import').hide();
						obj.errorStatus = false;
						obj.SendDataSecondary();
					},
					error: function(){
						timeBlock.innerHTML = '';
						obj.TimeoutOnAutoConinue();
					}
				});
				return;
			}
		}
		setTimeout(function(){obj.TimeoutOnAutoConinue();}, 1000);
	},
	
	SendDataSecondary: function()
	{
		var obj = this;
		if(this.post.STEPS_DELAY)
		{
			setTimeout(function(){
				obj.SendData();
			}, parseInt(this.post.STEPS_DELAY) * 1000);
		}
		else
		{
			obj.SendData();
		}
	},
	
	OnLoad: function(data)
	{
		data = $.trim(data);
		var returnLabel = '<!--module_return_data-->';
		if(data.indexOf(returnLabel)!=-1)
		{
			data = $.trim(data.substr(data.indexOf(returnLabel) + returnLabel.length));
		}
		if(data.indexOf('{')!=0)
		{
			if(data.indexOf("'bitrix_sessid':'")!=-1)
			{
				var sessid = data.substr(data.indexOf("'bitrix_sessid':'") + 17);
				sessid = sessid.substr(0, sessid.indexOf("'"));
				if(sessid.length > 0) this.post.sessid = sessid;
			}
			var obj = this;
			setTimeout(function(){obj.SendDataSecondary();}, 2000);
			return true;
		}
		try {
			eval('var result = '+data+';');
		} catch (err) {
			var result = false;
		}
		if(typeof result == 'object')
		{
			if(result.sessid)
			{
				$('#sessid').val(result.sessid);
				this.post.sessid = result.sessid;
			}
			
			if(typeof result.errors == 'object' && result.errors.length > 0)
			{
				$('#block_error').show();
				for(var i=0; i<result.errors.length; i++)
				{
					$('#res_error').append('<div>'+result.errors[i]+'</div>');
				}
			}
			
			if(result.action=='continue')
			{
				this.UpdateStatus(result.params);
				this.params = result.params;
				this.SendDataSecondary();
				return true;
			}
		}
		else
		{
			this.SendDataSecondary();
			return true;
		}

		this.UpdateStatus(result.params, true);
		BX.closeWait(null, this.wait);
	
		return false;
	}
}

$(document).ready(function(){
	if($('#preview_file').length > 0)
	{
		var post = $('#preview_file').closest('form').serialize() + '&ACTION=SHOW_PREVIEW_LIST';
		$.post(window.location.href, post, function(data){
			$('#preview_file').html(data);
			if($('.kda-ie-tbl:not([data-init])').length > 0)
			{
				EList.Init();
				$('.kda-ie-tbl').attr('data-init', 1);
			}
		});
	}
	
	if($('#esol-rr-updates-message').length > 0)
	{
		$.post('/bitrix/admin/'+esolRRModuleFilePrefix+'_redirect_list.php?lang='+BX.message('LANGUAGE_ID'), 'MODE=AJAX&ACTION=SHOW_MODULE_MESSAGE', function(data){
			data = $(data);
			var inner = $('#esol-rr-updates-message-inner', data);
			if(inner.length > 0 && inner.html().length > 0)
			{
				$('#esol-rr-updates-message-inner').replaceWith(inner);
				$('#esol-rr-updates-message').show();
			}
		});
	}
});