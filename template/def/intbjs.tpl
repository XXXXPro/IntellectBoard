<script><!--
 window.IntB_params = {
   basedir:'{{ url('') }}',{%
   if draft_name %}draft: '{{ draft_name }}',{% endif %}{% if smiles %}
   emoticonsRoot : '{{ url('sm/') }}',
   emoticons : { {%
   if smiles.dropdown %}
    dropdown : { {% for item in smiles.dropdown %}"{{ item.code }}":"{{ item.file }}",{% endfor %} }, {% endif %}{%
   if smiles.more %}
    more : { {% for item in smiles.more %}"{{ item.code }}":"{{ item.file }}",{% endfor %} },{% endif %}{%
   if smiles.hidden %}
     hidden : { {% for item in smiles.hidden %}"{{ item.code }}":"{{ item.file }}",{% endfor %} },{%
   endif %}
   },{% endif %}
   wysiwyg: '{{ get_opt('wysiwyg','user') }}',
   longposts: {{ get_opt('longposts','user') }},
   jquery_cdn: '{{ jquery_cdn }}',
   upload_max_filesize:  {{ upload_max_filesize }},
   post_max_size: {{ post_max_size }},
   max_file_uploads: {{ max_file_uploads }}
  };
--></script>
<script src="{{ url('js/intb.js') }}" defer="defer"></script>