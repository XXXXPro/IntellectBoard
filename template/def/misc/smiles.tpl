{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<link href="{{ style('post.css') }}" rel="stylesheet" />
<style type="text/css">
#ib_all .post { border: none; background: none }
#ib_all #misc_help h3 { clear: both; padding-top: 15px; text-align: center }
#ib_all #misc_help p { padding-left: 10px }
#ib_all #misc_help ul { display: flex; flex-wrap: wrap; list-style: none; margin: 0; padding: 0 }
#ib_all .bcode_help li span { min-width: 48px; padding: 5px; margin: 0; display: inline-block; box-sizing: border-box; text-align: right }
#ib_all .bcode_help li { min-width: 160px; padding: 5px 0; margin: 0; box-sizing: border-box; }
</style>
{% endblock %}
{% block content %}
<div id="misc_help" class="bcode_help">
<h1>Смайлики форума и их обозначения</h1>

<p>Используйте коды, приведенные ниже, чтобы вставить соответствующие графические изображения.<br /> 
Обратите внимание: в некоторых разделах смайлики могут быть отключены или ограничены по количеству.</p>

<ul>
{% for item in smiles.dropdown %}<li><span><img src="{{ url('sm/'~item.file) }}" alt="{{ item.code }} {{ item.descr }}"/></span><kbd>{{ item.code }}</kbd>{% if item.descr %} <small>({{item.descr}})</small>{% endif %}</dd>{% endfor %}
{% for item in smiles.more %}<li><span><img src="{{ url('sm/'~item.file) }}" alt="{{ item.code }} {{ item.descr }}"/></span><kbd>{{ item.code }}</kbd>{% if item.descr %} <small>({{item.descr}})</small>{% endif %}</dd>{% endfor %}
</ul>
</div>
{% endblock %}
