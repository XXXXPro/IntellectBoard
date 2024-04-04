{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block content %}
<script>
  (function() {
    var cx = '{{ google_id }}';
    var gcse = document.createElement('script');
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
        '//www.google.com/cse/cse.js?cx=' + cx;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gcse, s);
  })();
</script>
<gcse:search></gcse:search>
<div><small>Примечание: поиск осуществляется только по публично-доступным разделам. <br />
В поисковом запросе можно пользоваться тем же синтаксисом, что и при обычном поиске в Google.<br />
В связи с тем, что индексация сайта происходит не в режиме реального времени, сообщения за последние несколько суток могут быть не найдены.
</small></div>
{% endblock %}
