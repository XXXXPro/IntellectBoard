{% extends 'main.tpl' %}
{% import 'macro.tpl' as macros %}
{% block css %}
<link rel="preload" as="style" href="{{ style('privmsg.css') }}" onload="this.rel='stylesheet'"/>
{% endblock %}
{% block content %}
<div id="privmsg_thread">
<h1>{{ thread.title }}</h1>
</div>
<div>
<form action="delete.htm" method="post" class="ib_form"><fieldset style="border:0 "><legend style="display:none"></legend>
<div class="pages right" style="clear: both">{% if thread.total==privmsg_pm|length %}Показаны все сообщения
{% else %}{% if show=='' %}Показаны непрочитанные сообщения. {% endif %}Показать сообщения за последние 
{% if show!='day' %}<a href="?show=day">сутки</a>{% else %}<b>сутки</b>{% endif %}, 
{% if show!='3days' %}<a href="?show=3days">3 суток</a>{% else %}<b>3 суток</b>{% endif %}, 
{% if show!='week' %}<a href="?show=week">неделю</a>{% else %}<b>неделю</b>{% endif %}, 
{% if show!='month' %}<a href="?show=month">месяц</a>{% else %}<b>месяц</b>{% endif %}, 
{% if show!='3months' %}<a href="?show=3months">3 месяца</a>{% else %}<b>3 месяца</b>{% endif %}, 
{% if show!='year' %}<a href="?show=year">год</a>{% else %}<b>год</b>{% endif %}, 
{% if show!='all' %}<a href="?show=all">все время</a>{% else %}<b>все время</b>{% endif %}.{% endif %}</div>
<div>В разговоре участвуют: {% for user in thread.users %}{% if loop.index>1%}, {% endif %}{{ macros.user(user.display_name,user.id,user.avatar) }}{% endfor %}</div>

{{ macros.sub_block(IntB_subactions['action_start']) }}

{% for item in privmsg_pm %}
<div class="pm fadeout" id="pm{{ item.id }}">
<ul class="postact"><li><a class="postdelete ajax confirm" href="delete.htm?del={{ item.id }}&amp;authkey={{ deletekey }}" title="Удалить"><i class="far fa-trash-alt"></i></a></li></ul>
<div class="pu">
{{ macros.avatar(item.uid,item.avatar,item.display_name) }} {{ macros.user(item.display_name,item.uid,item.gender) }} {{ item.postdate|longdate }}
{# <div class="group{{item.level}}">{{ item.user_title }}</div> #}
</div>
{{ item.text|raw }}
</div>
{% endfor %}
{% if privmsg_pm|length==0 %}<div class="pm">За выбранный период нет ни одного сообщения.</div>{% endif %}
</fieldset></form>

{{ macros.sub_block(IntB_subactions['action_end']) }}

{% if recepients|length>0 %}{% include 'privmsg/pm_form.tpl' %}{% else %}
<p>Вы не можете отправить сообщение в эту тему: все остальные участники отписались от нее или внесли вас в черный список</p>
{% endif %}
<a class="actionbtn warnbtn unsubscribe confirm" href="unsubscribe.htm"><i class="fas fa-times-circle"></i> Выйти из диалога и удалить его</a>

{% endblock %}
