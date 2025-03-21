/*! head.load - v1.0.3 */
(function(n,t){"use strict";function w(){}function u(n,t){if(n){typeof n=="object"&&(n=[].slice.call(n));for(var i=0,r=n.length;i<r;i++)t.call(n,n[i],i)}}function it(n,i){var r=Object.prototype.toString.call(i).slice(8,-1);return i!==t&&i!==null&&r===n}function s(n){return it("Function",n)}function a(n){return it("Array",n)}function et(n){var i=n.split("/"),t=i[i.length-1],r=t.indexOf("?");return r!==-1?t.substring(0,r):t}function f(n){(n=n||w,n._done)||(n(),n._done=1)}function ot(n,t,r,u){var f=typeof n=="object"?n:{test:n,success:!t?!1:a(t)?t:[t],failure:!r?!1:a(r)?r:[r],callback:u||w},e=!!f.test;return e&&!!f.success?(f.success.push(f.callback),i.load.apply(null,f.success)):e||!f.failure?u():(f.failure.push(f.callback),i.load.apply(null,f.failure)),i}function v(n){var t={},i,r;if(typeof n=="object")for(i in n)!n[i]||(t={name:i,url:n[i]});else t={name:et(n),url:n};return(r=c[t.name],r&&r.url===t.url)?r:(c[t.name]=t,t)}function y(n){n=n||c;for(var t in n)if(n.hasOwnProperty(t)&&n[t].state!==l)return!1;return!0}function st(n){n.state=ft;u(n.onpreload,function(n){n.call()})}function ht(n){n.state===t&&(n.state=nt,n.onpreload=[],rt({url:n.url,type:"cache"},function(){st(n)}))}function ct(){var n=arguments,t=n[n.length-1],r=[].slice.call(n,1),f=r[0];return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(f?(u(r,function(n){s(n)||!n||ht(v(n))}),b(v(n[0]),s(f)?f:function(){i.load.apply(null,r)})):b(v(n[0])),i)}function lt(){var n=arguments,t=n[n.length-1],r={};return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(u(n,function(n){n!==t&&(n=v(n),r[n.name]=n)}),u(n,function(n){n!==t&&(n=v(n),b(n,function(){y(r)&&f(t)}))}),i)}function b(n,t){if(t=t||w,n.state===l){t();return}if(n.state===tt){i.ready(n.name,t);return}if(n.state===nt){n.onpreload.push(function(){b(n,t)});return}n.state=tt;rt(n,function(){n.state=l;t();u(h[n.name],function(n){f(n)});o&&y()&&u(h.ALL,function(n){f(n)})})}function at(n){n=n||"";var t=n.split("?")[0].split(".");return t[t.length-1].toLowerCase()}function rt(t,i){function e(t){t=t||n.event;u.onload=u.onreadystatechange=u.onerror=null;i()}function o(f){f=f||n.event;(f.type==="load"||/loaded|complete/.test(u.readyState)&&(!r.documentMode||r.documentMode<9))&&(n.clearTimeout(t.errorTimeout),n.clearTimeout(t.cssTimeout),u.onload=u.onreadystatechange=u.onerror=null,i())}function s(){if(t.state!==l&&t.cssRetries<=20){for(var i=0,f=r.styleSheets.length;i<f;i++)if(r.styleSheets[i].href===u.href){o({type:"load"});return}t.cssRetries++;t.cssTimeout=n.setTimeout(s,250)}}var u,h,f;i=i||w;h=at(t.url);h==="css"?(u=r.createElement("link"),u.type="text/"+(t.type||"css"),u.rel="stylesheet",u.href=t.url,t.cssRetries=0,t.cssTimeout=n.setTimeout(s,500)):(u=r.createElement("script"),u.type="text/"+(t.type||"javascript"),u.src=t.url);u.onload=u.onreadystatechange=o;u.onerror=e;u.async=!1;u.defer=!1;t.errorTimeout=n.setTimeout(function(){e({type:"timeout"})},7e3);f=r.head||r.getElementsByTagName("head")[0];f.insertBefore(u,f.lastChild)}function vt(){for(var t,u=r.getElementsByTagName("script"),n=0,f=u.length;n<f;n++)if(t=u[n].getAttribute("data-headjs-load"),!!t){i.load(t);return}}function yt(n,t){var v,p,e;return n===r?(o?f(t):d.push(t),i):(s(n)&&(t=n,n="ALL"),a(n))?(v={},u(n,function(n){v[n]=c[n];i.ready(n,function(){y(v)&&f(t)})}),i):typeof n!="string"||!s(t)?i:(p=c[n],p&&p.state===l||n==="ALL"&&y()&&o)?(f(t),i):(e=h[n],e?e.push(t):e=h[n]=[t],i)}function e(){if(!r.body){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(e,50);return}o||(o=!0,vt(),u(d,function(n){f(n)}))}function k(){r.addEventListener?(r.removeEventListener("DOMContentLoaded",k,!1),e()):r.readyState==="complete"&&(r.detachEvent("onreadystatechange",k),e())}var r=n.document,d=[],h={},c={},ut="async"in r.createElement("script")||"MozAppearance"in r.documentElement.style||n.opera,o,g=n.head_conf&&n.head_conf.head||"head",i=n[g]=n[g]||function(){i.ready.apply(null,arguments)},nt=1,ft=2,tt=3,l=4,p;if(r.readyState==="complete")e();else if(r.addEventListener)r.addEventListener("DOMContentLoaded",k,!1),n.addEventListener("load",e,!1);else{r.attachEvent("onreadystatechange",k);n.attachEvent("onload",e);p=!1;try{p=!n.frameElement&&r.documentElement}catch(wt){}p&&p.doScroll&&function pt(){if(!o){try{p.doScroll("left")}catch(t){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(pt,50);return}e()}}()}i.load=i.js=ut?lt:ct;i.test=ot;i.ready=yt;i.ready(r,function(){y()&&u(h.ALL,function(n){f(n)});i.feature&&i.feature("domloaded",!0)})})(window);
/*
//# sourceMappingURL=head.load.min.js.map
*/

// Intellect Board Script

function IntB_main(opts) {
//  head.load('https://use.fontawesome.com/releases/v5.0.8/css/all.css');
  // функция вставки данных в поле ввода или визуальный редактор, если он используется
  this.get_quote = function (target) {
    var pnode = $(target).parents('.postin');
    var post_id = false;
    var username = '';
    if (pnode) { 
      post_id = $(target).parents('.post').get(0).id.replace('p', '');
      username = pnode.find('.pu .username').text();
      if (post_id) username = username+','+post_id;
    }
    self.stored_user = username;
    try {
      var selection = window.getSelection();
      var quoted = selection.toString();
      if (quoted) {
        if ($(selection.anchorNode).parents('.postin').get(0) != pnode.get(0) ||
          $(selection.anchorNode).parents('.postin').get(0) != pnode.get(0)) quoted = '';
      }      
      return quoted;
    }
    catch (ex) {
    }
  }

  this.paste_quoted = function(quoted) {
    if (!quoted || quoted==null) return;
    if (opts.wysiwyg && opts.wysiwyg!='0' && $('.bbcode').sceditor('instance').val()!=quoted) {
      if ($('.bbcode').sceditor('instance').val().length!=0) quoted="\n"+quoted;
      $('.bbcode').sceditor('instance').insert(quoted, "\n ", true, true);
    }
    else {
      head.load([opts.basedir+'js/jquery.selection.js'], function() {
        var postform =$('form.postform').find('textarea[name="post[text]"]');
        if (postform.val()!=quoted) {
          postform.selection('insert',{ text: "\r\n"+quoted+"\r\n", mode: 'before' });
        }
      });
    }
  };
  self=this;

  // переход к следующей/предыдущей странице или на уровень выше
  $(document).keyup(function(e) {
    if (e.ctrlKey) {
      if ($('textarea:focus').length || $('input[type=text]:focus').length) return;
      if(e.keyCode==39) { var link=$('head link[rel=next]').attr('href'); if (link) window.location.href=link;}
      if(e.keyCode==37) { var link=$('head link[rel=prev]').attr('href'); if (link) window.location.href=link;}
      if (e.keyCode==38) { var link=$('head link[rel=up]').attr('href'); if (link) window.location.href=link;}
    }
  });
  // отпрвка форм по Ctrl+Enter
  $('textarea').keyup(function(e) { if (e.ctrlKey && e.keyCode==13) $(this).closest('form').submit(); });
  // подтверждение опасных действий
  $('.confirm:not(.ajax)').click(function (e) {
      return (confirm('Вы действительно хотите выполнить это действие?'));
  });

  // действия по разворачиванию частей сообщений: цитат, блоков кода и спойлеров
  $('blockquote, code').each(function (k,v) {
    function setFolding() {
      if (v.scrollHeight > v.clientHeight && !$('> .foldlink', v.parentNode).length) {
        $('<a href="#" class="foldlink">Развернуть</a>').insertBefore(v).click(function (e) {
	        e.preventDefault();
	        $(v).toggleClass('unfolded');
        });
      }
    }
    setFolding();
    $('img', v).on('load', setFolding);
  });

  $('.ptext .cutlink').click(function (e){
     e.preventDefault();
     $(e.target).next().show();
     $(e.target).hide();
  });
  $('.ptext .spoiler').click(function (e){
     e.preventDefault();
     $(e.target).next().toggleClass('invis');
  });

  // пометка сообщений для последующего переноса или удаления без перезагрузки страницы
  $('.postact .postmark').click(function(e){
     var targ=e.target.tagName=="A" ? e.target : e.target.parentNode;
     jQuery.ajax(targ.href+'&ajax=1',{ complete: function(data,status,xhr) {
        if (data.responseJSON) {
          if (data.responseJSON.result=='marked') {
            targ.href+='&unmark=1';
            $(targ).closest('.post').addClass('marked');
            $(targ.firstChild).addClass('fa-minus-square').removeClass('fa-plus-square').attr('title','Снять пометку');
          }
          else if (data.responseJSON.result=='unmarked') {
            targ.href=targ.href.replace('&unmark=1','');
            $(targ).closest('.post').removeClass('marked');
            $(targ.firstChild).addClass('fa-plus-square').removeClass('fa-minus-square').attr('title','Пометить для переноса');
          }
        }
     }
     });
     return false;
  });
  // Свертывание/развертывание сообщений
  if (opts.longposts==1) $('.post').addClass('collapsed');
  else if (opts.longposts==2) $('.post.flood').addClass('collapsed');
  $('.post .ptop').prepend('<span class="fold" title="Щелкните, чтобы свернуть или развернуть сообщение"></span>').click(function (e) {
      if ($(e.target).hasClass('postnumber')) return true;
      $(e.target).closest('.post').toggleClass('collapsed');
      return false;
  });
  // эффект accrordion для форм
  $('.accordion fieldset').not(':first').find('div').not('.submit').hide();
  $('.accordion fieldset:first').addClass('acc_active');
  $('.accordion legend').click(function (e) {
    if (!$(e.target).closest('fieldset').hasClass('acc_active')) {
      $(e.target).closest('.ibform').find('fieldset.acc_active').removeClass('acc_active');
      $(e.target).closest('fieldset').addClass('acc_active');
      $(e.target).closest('.ibform').find('div').not('.submit').slideUp(250);
      $(e.target).parent().find('div').slideDown(250);
    }
  });

  // AJAX-рейтинг без обновления страницы
  $('.prating a').click(function (e) {
     var tg = e.target;
     if (e.target.tagName!='A') tg=e.target.parentNode;
     if (!$(tg).hasClass('norate')) {
       jQuery.ajax(tg.href+'&ajax=1',{ complete: function(data,status,xhr) {
         if (data.responseJSON) {
           if (data.responseJSON.result=='done') {
             $(tg).closest('.prating').find('.prvalue').text(data.responseJSON.value);
             $(tg).closest('.prating').find('a').attr('href','#');
           }
           $(tg).closest('.prating').find('a').attr('title',data.responseJSON.message);
           $(tg).closest('.prating').find('a').addClass('norate');
         }
       }});
     }
     return false;
  });
  // удаление объекта со страницы, если есть класс confirm, то с подтверждением
  $('.ajax').click(function (e) {
    var target=e.target
    if (e.target.tagName!='A') target=$(target).parents('a')[0];
    var flag = true;
    if ($(target).hasClass('confirm')) flag=confirm('Вы действительно хотите выполнить это действие?');
    if (flag) {
      jQuery.ajax(target.href+'&ajax=1',{ complete: function(data,status,xhr) {
        if (data.responseJSON && data.responseJSON.result=='done') {
          $(target).closest('.fadeout').fadeOut();
        }
      }});
    }
    return false;
  });

  $('.postform input[type="file"]').change(function (e) {
      let files = e.target.files;
      let total_size = 0;
      let errors = '';
      for (let i=0; i<files.length; i++) {
        total_size = total_size + files[i].size;
        if (files[i].size>opts.upload_max_filesize) errors+='Размер файла '+files[i].name+' превышает лимит в '+parseInt(opts.upload_max_filesize/1024)+" Кб!\n";
      }
      if (opts.max_file_uploads && files.length>opts.max_file_uploads) errors+='Количество файлов превышает серверный лимит, равный '+opts.max_file_uploads+"!\n";
      if (e.target.dataset.maxfiles && files.length>e.target.dataset.maxfiles) errors+='Количество файлов превышает оставшееся число фото в альбоме';
      if (total_size>opts.post_max_size) errors+='Суммарный размер файлов равен '+parseInt(total_size/1024)+' Кб. Это  превышает лимит загрузки в '+parseInt(opts.post_max_size/1024)+' Кб! Отмените часть загрузок и загрузите позже, отредактировав сообщение.';
      e.target.setCustomValidity(errors);
    }
  );

  self.load_more = function (e) {
    var url =e.target.href;
    if (url.indexOf('#')>0) url=url.replace('#','&ajax=1#');
    else url=url+'&ajax=1';
    jQuery.ajax(url,{ complete: function(data,status,xhr) {
      $(e.target).hide();
      jQuery(e.target).after(data.responseText);
      history.pushState(null, null, url.replace('&ajax=1',''));
      $('.load_more').click(self.load_more);      
    }});
    return false;
  };
  $('.load_more').click(self.load_more);

  //обработка различных классов, требующих подгрузки специальных скриптов
  // визуальный редактор для HTML
  var scepath=opts.basedir+'js/sceditor/';
  var wysiwyg_nodes=$('.wysiwyg');
  if (wysiwyg_nodes.length) {
    head.load([scepath+'minified/themes/default.min.css',scepath+'minified/jquery.sceditor.min.js',
      scepath+'languages/ru.js'],function() {
      wysiwyg_nodes.sceditor({
        height: '600px',
        locale: "ru",
        style: scepath+"minified/jquery.sceditor.default.min.css",
        toolbarExclude: 'emoticon,print,date,time,ltr,rtl',
        emoticonsRoot : opts.emoticonsRoot,
        emoticons : opts.emoticons,
      });
      wysiwyg_nodes.sceditor('instance').keyDown(function(e) {
          if (e.ctrlKey && e.keyCode==13) $(e.target).closest('form').submit();
      });
    });
  }
  // визуальный редактор для bbcode
  var bbcode_nodes=$('.bbcode');
  if (opts.draft && opts.draft_reset && typeof(Storage)!=="undefined") localStorage.setItem('IntB_'+opts.draft,null); // сброс черновика, если предыдущее сообщение было успешно отправлено
  if (opts.wysiwyg && opts.wysiwyg!='0' && bbcode_nodes.length) {
    head.load([scepath+'minified/themes/default.min.css',scepath+'minified/jquery.sceditor.min.js',
      opts.basedir+'js/sceditor/minified/formats/bbcode.js',scepath+'languages/ru.js'],function() {
      var exclude = 'print,date,time,ltr,rtl,table,indent,cut,copy,paste,pastetext,horizontalrule,outdent'+(typeof(opts.emoticons)==="undefined" ? ",emoticon" : "");
      if (/Android|webOS|Phone|iPad|iPod|Tablet|BlackBerry|Mobile|Opera Mini/i.test(navigator.userAgent)) {
        exclude+=",left,center,right,justify,subscript,superscript,font,size,color,removeformat";
      }
      bbcode_nodes.sceditor({
        format: 'bbcode',
        locale: "ru",
        style: opts.basedir+"js/sceditor/minified/jquery.sceditor.default.min.css",
        toolbarExclude: exclude,
        emoticonsEnabled : typeof(opts.emoticons)!=="undefined",
        emoticonsRoot : opts.emoticonsRoot,
        emoticons : opts.emoticons,
        autoExpand : true,
        resizeEnabled : true
      });
      if (opts.wysiwyg==1) bbcode_nodes.sceditor('instance').sourceMode(true);
      bbcode_nodes.sceditor('instance').keyDown(function(e) {        
          if (e.ctrlKey && e.keyCode==13) {
            bbcode_nodes.sceditor('instance').updateOriginal();
            $(e.target).closest('form').submit();
          }
      });
      if (typeof(Storage)!=="undefined" && opts.draft) {
        $('.bbcode').val(localStorage.getItem("IntB_"+opts.draft));
        bbcode_nodes.sceditor('instance').blur(function(){
          localStorage.setItem('IntB_'+opts.draft,$('.bbcode').sceditor('instance').val());
        });
        setInterval(function () {
          localStorage.setItem('IntB_'+opts.draft,$('.bbcode').sceditor('instance').val());
        },15000);
      }
      var mini_nodes=$('.miniform');
      if (mini_nodes.length) {
         mini_nodes.find('.sceditor-toolbar').hide();
         mini_nodes.find('.submit').hide();
         mini_nodes.find('.captcha').hide();
         mini_nodes.find('.user_field').hide();
         bbcode_nodes.sceditor('instance').focus(function() {
           mini_nodes.find('.sceditor-toolbar').slideDown();
           mini_nodes.find('.submit').slideDown();
           mini_nodes.find('.captcha').slideDown();
           mini_nodes.find('.user_field').slideDown();
         });
      }
//      $('#ib_all div.sceditor-container').show();
    });
  }

  // восстановление сохраненных данных и организация автосохранения, если виз. редактор выключен
  if (bbcode_nodes.length && typeof(Storage)!=="undefined" && (!opts.wysiwyg || opts.wysiwyg=='0')) {
    this.paste_quoted(localStorage.getItem("IntB_"+opts.draft));
    setInterval(function () {
      localStorage.setItem('IntB_'+opts.draft,bbcode_nodes.val());
    },15000);
    bbcode_nodes.change(function (e) {
      localStorage.setItem('IntB_'+opts.draft,bbcode_nodes.val());
    });
  }
  // сброс сохраненного черновика при отправке формы
  if (bbcode_nodes.length && typeof(Storage)!=="undefined") {
    bbcode_nodes.parents('form').submit(function (e) {
      localStorage.removeItem('IntB_'+opts.draft);
    });
  }
  var mini_bbcode_nodes=$('.mini_bbcode');

  var date_nodes = $('.date');
  if (date_nodes.length) {
    head.load([opts.basedir+"js/date/default.css",opts.basedir+'js/date/zebra_datepicker.js'], function() {
      date_nodes.Zebra_DatePicker({
        days : ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        days_abbr : ['вс','пн','вт','ср','чт','пт','сб'],
        format : 'd.m.Y',
        inside : false,
        open_on_focus : true,
        months : ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        months_abbr : ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек']
      });
    });
  }
  var date_nodes = $('.datetime');
  if (date_nodes.length) {
    head.load([opts.basedir+"js/date/default.css",opts.basedir+'js/date/zebra_datepicker.js'], function() {
      date_nodes.Zebra_DatePicker({
        days : ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        days_abbr : ['вс','пн','вт','ср','чт','пт','сб'],
        format : 'd.m.Y G:i',
        inside : false,
        open_on_focus : true,
        months : ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        months_abbr : ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек']
      });
    });
  }
  var lightbox_nodes = $(".post a[href$='.jpg'], .post a[href$='.png'], .post a[href$='.gif'], .attach_preview a.lightbox");
  if (lightbox_nodes.length) {
    head.load([opts.basedir+'js/colorbox/colorbox.css',opts.basedir+'js/colorbox/jquery.colorbox-min.js',opts.basedir+'js/colorbox/i18n/jquery.colorbox-ru.js'], function() {
     lightbox_nodes.attr('rel','attach').colorbox({'rel':'attach', 'maxWidth': '100%', 'maxHeight': '100%' });
    });
  }
  $('.pu .username').click(function (e) {
    e.preventDefault();
    self.paste_quoted('[b]'+e.target.innerHTML+'[/b],');
  });
  var postquote_nodes = $('a.postquote');
  if (postquote_nodes.length) {
      postquote_nodes.click(function (e) {
        e.preventDefault();
        let quoted = self.get_quote(e.target);
        if (quoted == '') alert('Выделите часть сообщения для цитирования и пользуйтесь той ссылкой "Цитировать", которая расположена рядом с соответствующим сообщением!');
        else {
          quoted = '[quote=' + self.stored_user + ']' + quoted + '[/quote]';
          self.paste_quoted(quoted);
        }
      });
  }
  if ($('#uLogin').length>0 && !/Android|webOS|Phone|iPad|iPod|Tablet|BlackBerry|Mobile|Opera Mini/i.test(navigator.userAgent)) {
    setTimeout(function(){head.load(['https://ulogin.ru/js/ulogin.js'])},0);
  }
  if ($('#uLogin_big').length>0) {
    setTimeout(function(){head.load(['https://ulogin.ru/js/ulogin.js'])},0);
  }

  $('.flipper').not(':checked').parent().next().hide();
  $('.flipper').click(function (e){
     $(e.target).parent().next().slideToggle();
  });

  $('#user_register input[name="basic[login]"]').change(function (e) {
      jQuery.ajax('check_login.htm?ajax=1&login='+encodeURI(e.target.value), { complete: function(data,status,xhr) {
          if (data.responseJSON.result=='done') {
              $('#check_result').addClass('msg_ok');
              $('#check_result').removeClass('msg_error');
              $('#check_result').text('✔').attr('title',data.responseJSON.message);
          }
          else {
              $('#check_result').removeClass('msg_ok');
              $('#check_result').addClass('msg_error')
              $('#check_result').text('✖').attr('title',data.responseJSON.message);
          }
      }});
  });

  $('.admin_menu_elm h3').click(function (e) {
    var elm=$(e.target).parent().find('ul');
    if (elm.is(":visible")) elm.slideUp();
    else elm.slideDown();
  });
  
  jQuery('img[data-src]').each(function (i,el) { el.src=el.dataset['src'] });
  $('form input[type="button"][name="preview"]').click(function(e) {
    var frm=e.target.form;
    var old_target = frm.target;
    var old_action = frm.action;
    var old_enctype= frm.enctype;
    var wnd = window.open("about:blank","preview_wnd","height=600,width=1004,menubar=no,toolbar=no,location=no,status=no");
    frm.target = "preview_wnd";
    frm.action="preview.htm";
    frm.enctype="application/x-www-form-urlencoded";
    frm.elements['authkey'].setAttribute('name', 'disabled_authkey'); // убираем ключ из формы, так как он не для preview, а для другого action
    bbcode_nodes.sceditor('instance').updateOriginal();
    frm.submit();
    frm.elements['disabled_authkey'].setAttribute('name', 'authkey');
    frm.target=old_target;
    frm.action=old_action;
    frm.enctype=old_enctype;
  });

  var sandwich_main = document.getElementById('intb_sandwich_main');
  if (sandwich_main) {
    jQuery('body').click(function (e) {
      var cur_elm = e.target;
      var in_sandwich = false;
      while (cur_elm.parentNode) {
        if (cur_elm == sandwich_main || cur_elm == sandwich_main.parentNode) {
          in_sandwich = true;
          break;
        }
        cur_elm = cur_elm.parentNode;
      }
      if (sandwich_main.checked && !in_sandwich) sandwich_main.checked = false;
    });
  }

  this.popup_menu = function(e,x,y) {
    var selection = window.getSelection();

    if (!selection.isCollapsed) {
      if ($(e.target).hasClass('ptext') || $(e.target).parents('.ptext').length>0) {
        var rect = selection.getRangeAt(0).getBoundingClientRect();
        $('#quotemenu').css({ 'top': (y)+"px",'left':(x)+"px"});
        $('#quotemenu').removeClass('invis');
        self.stored_quote = self.get_quote(selection.anchorNode);
      }
      else $('#quotemenu').addClass('invis');
    }
  }

  document.addEventListener("mouseup", function (e) {
    self.popup_menu(e,window.scrollX+e.clientX,window.scrollY+e.clientY);
  });

  document.addEventListener("touchend", function (e) {
    const touches = e.changedTouches;
    if (touches[0]) {
      self.popup_menu(e,touches[0].pageX,touches[0].pageY);
    }
  });

  if (!navigator.canShare) $('#quotemenu_share').addClass('invis');
  if ($('form.postform').find('textarea[name="post[text]"]').length == 0) $('#quotemenu_quote').addClass('invis');

  document.addEventListener('click', function() {
    var selection = window.getSelection();
    if (selection.isCollapsed) {
      $('#quotemenu').addClass('invis');
    }
  });

  document.getElementById('quotemenu_quote').addEventListener("click", function(e) {
    self.paste_quoted('[quote=' + self.stored_user + ']' + self.stored_quote + '[/quote]');
    $('#quotemenu').addClass('invis');
  });
  document.getElementById('quotemenu_copy').addEventListener("click", async function (e) {
    if (navigator.clipboard) navigator.clipboard.writeText(self.stored_quote + "\nИсточник: " +document.location.href);
    $('#quotemenu').addClass('invis');
  });
  document.getElementById('quotemenu_share').addEventListener("click", async function (e) {
    if (navigator.canShare && navigator.canShare(self.stored_quote)) await navigator.share(self.stored_quote);
    $('#quotemenu').addClass('invis');
  });
  document.getElementById('quotemenu_vk').addEventListener("click", async function (e) {
    window.open('https://vk.com/share.php?url=' + encodeURIComponent(document.location.href) + '&comment=' + encodeURIComponent(self.stored_quote))
    $('#quotemenu').addClass('invis');
  });  

  var mathtex = $('.mathtex');
  var asciimath = $('.asciimath');
  if (mathtex.length>0 || asciimath.length>0) {
    var mathloader = ['output/chtml'];
    if (mathtex.length > 0) mathloader.push('input/tex')
    if (asciimath.length > 0) mathloader.push('input/asciimath');
    MathJax = {
      loader: { load: mathloader },
      options: {
        renderActions: {
          addMenu: []
        }
      }      
    } 
    head.load(["https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"]);
  }

  document.dispatchEvent(new CustomEvent("IntBLoaded"));
}

head.load(window.IntB_params.jquery_cdn, function() {
  intb_loader = new IntB_main(window.IntB_params);
});