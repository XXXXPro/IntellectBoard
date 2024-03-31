{% extends intb.is_ajax ? 'ajax.tpl' : 'main.tpl' %}
{% block css %}
<link href="{{ style('post.css') }}" rel="stylesheet" />
<style type="text/css">
#ib_all .post { border: none; background: none }
#ib_all #misc_help h3 { clear: both; padding-top: 15px; text-align: center }
#ib_all #misc_help dt { font-weight: bold }
#ib_all .bcode_help dt { width: 50%; padding: 5px 0; float: left; clear: left; margin: 0; border-top: #eee 1px solid; font-weight: bold }
#ib_all .bcode_help dd { width: 50%; padding: 5px 0; float: left; ; margin: 0; border-top: #eee 1px solid }
#ib_all .bcode_help dd small { display: block; color: #333 }
#ib_all .bcode_help dt span { color: #c00 }
#ib_all .bcode_help dd * { max-width: 100% }
#ib_all .bcode_help dt div {overflow: auto }
@media screen and (max-width: 480px) {
    #ib_all .bcode_help dt, #ib_all .bcode_help dd { width: auto; float: none }
    #ib_all .bcode_help dd { border-top: 0 }
    #ib_all .bcode_help dt { overflow: auto }
}
</style>
{% endblock %}
{% block content %}
<div id="misc_help">
{{ help|raw }}
</div>
{% endblock %}
