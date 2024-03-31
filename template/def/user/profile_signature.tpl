{% import 'macro.tpl' as macros %}
  <fieldset><legend>Подпись</legend>
     <div><label><span>Подпись</span>{{ macros.textarea('basic[signature]',formdata.basic.signature,4,60) }}</label><br />
     <small class="center">Подпись — это короткий текст, который выводится под каждым вашим сообщением. В ней можно использовать некоторые теги BBCode.</small>
     </div>
  </fieldset>

