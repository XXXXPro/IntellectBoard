<div class="poll">
{% if (poll.allow_vote) %}
<form action="vote.htm" method="get"><fieldset><legend></legend>
<label class="quest">{{ poll.question }}</label><br />
{% for variant in poll.variants %}
<label><input type="radio" name="vote" value="{{ variant.id }}" /> {{ variant.text }}</label><br />
{% endfor %}
{% if not poll.closed %}<div class="submit"><button type="submit">Проголосовать</button></div>{% endif %}
</fieldset></form>
{% else %}
<table class="design"><col /><col /><col />
<tbody>
<tr><td class="quest">{{ poll.question }}</td><td></td></tr>
{% for variant in poll.variants %}
<tr><td{% if poll.pvid==variant.id %} class="myresult"{% endif %}>{{ variant.text }}</td><td>{{ variant.count }}</td>
<td style="text-align: left"><div class="pollbar{{ loop.index % 6 }}" style="width: {% if variant.count>0 and poll.max>0 %}{{ sprintf("%d",variant.count/poll.max*100) }}%{% else %}5px{% endif %}">&nbsp;</div></td></tr>
{% endfor %}
</tbody></table>
{% endif %}
{% if poll.endtime and not poll.closed %}<p class="pollend">Опрос завершится {{ poll.endtime|longdate }}.</p>
{% elseif poll.closed %}Опрос завершился {{ poll.endtime|longdate }}. Новые голосования не принимаются.</p>{% endif %}
</div>
