{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block content %}
<div class="ya-site-form ya-site-form_inited_no" 
onclick="return {'action':'./','arrow':true,'bg':'#ffcc00','fontsize':12,'fg':'#000000','language':'ru','logo':'rb',
'publicname':'Yandex Site Search #1881412','suggest':false,'target':'_self','tld':'ru','type':2,
'usebigdictionary':true,'searchid': {{ yandex_id }},'webopt':false,'websearch':false,'input_fg':'#000000','input_bg':'#FFFFFF',
'input_fontStyle':'normal','input_fontWeight':'normal','input_placeholder':null,'input_placeholderColor':'#000000',
'input_borderColor':'#7F9DB9'}"><form action="http://yandex.ru/sitesearch" method="get" target="_self">
<input type="hidden" name="searchid" value="{{ yandex_id }}"/><input type="hidden" name="l10n" value="ru"/>
<input type="hidden" name="reqenc" value=""/><input type="text" name="text" value=""/>
<input type="submit" value="Найти"/></form></div>
<style type="text/css">.ya-page_js_yes .ya-site-form_inited_no { display: none; }</style>
<script type="text/javascript">(function(w,d,c){var s=d.createElement('script'),h=d.getElementsByTagName('script')[0],e=d.documentElement;if((' '+e.className+' ').indexOf(' ya-page_js_yes ')===-1){e.className+=' ya-page_js_yes';}s.type='text/javascript';s.async=true;s.charset='utf-8';s.src=(d.location.protocol==='https:'?'https:':'http:')+'//site.yandex.net/v2.0/js/all.js';h.parentNode.insertBefore(s,h);(w[c]||(w[c]=[])).push(function(){Ya.Site.Form.init()})})(window,document,'yandex_site_callbacks');</script>

<div id="ya-site-results" onclick="return {'tld': 'ru','language': 'ru','encoding': '','htmlcss': '1.x','updatehash': true}"></div>
<script type="text/javascript">(function(w,d,c){var s=d.createElement('script'),h=d.getElementsByTagName('script')[0];s.type='text/javascript';s.async=true;s.charset='utf-8';s.src=(d.location.protocol==='https:'?'https:':'http:')+'//site.yandex.net/v2.0/js/all.js';
h.parentNode.insertBefore(s,h);(w[c]||(w[c]=[])).push(function(){Ya.Site.Results.init();})})(window,document,'yandex_site_callbacks');</script>
<div><small>Примечание: поиск осуществляется только по публично-доступным разделам. <br />
В поисковом запросе можно пользоваться тем же синтаксисом, что и при обычном поиске в Google.<br />
В связи с тем, что индексация сайта происходит не в режиме реального времени, сообщения за последние несколько суток могут быть не найдены.
</small></div>
{% endblock %}
