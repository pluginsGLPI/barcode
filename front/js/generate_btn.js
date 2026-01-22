(function(){
  document.addEventListener('DOMContentLoaded', function(){
    try{
      var path = window.location.pathname || '';
      var page = path.split('/').pop();
      var map = {
        'computer.php': 'Computer',
        'computers.php': 'Computer',
        'monitor.php': 'Monitor',
        'monitors.php': 'Monitor',
        'phone.php': 'Phone',
        'phones.php': 'Phone',
        'computer.form.php': 'Computer',
        'monitor.form.php': 'Monitor',
        'phone.form.php': 'Phone'
      };
      var itemtype = map[page];
      if (!itemtype) return; // not an asset page we care about

      // build button
      var btn = document.createElement('a');
      btn.className = 'glpi_button';
      btn.style.marginLeft = '10px';
      btn.style.display = 'inline-block';
      btn.style.padding = '6px 12px';
      btn.style.background = '#f0ad4e';
      btn.style.color = '#fff';
      btn.style.borderRadius = '4px';
      btn.style.textDecoration = 'none';
      btn.style.fontWeight = '600';
      btn.target = '_blank';
      // If we are on an item's form page, add single-item parameter
      var params = new URLSearchParams(window.location.search);
      if (params.has('id') && page.indexOf('.form.php') !== -1) {
        var id = params.get('id');
        btn.href = (window.location.origin || window.location.protocol + '//' + window.location.host) + '/marketplace/barcode/front/generate_by_type.php?itemtype=' + encodeURIComponent(itemtype) + '&singleid=' + encodeURIComponent(id);
        btn.textContent = 'Generate QR for this ' + itemtype;
      } else {
        btn.href = (window.location.origin || window.location.protocol + '//' + window.location.host) + '/marketplace/barcode/front/generate_by_type.php?itemtype=' + encodeURIComponent(itemtype);
        btn.textContent = 'Generate QR for all ' + itemtype + 's';
      }

      // find a container to insert the button
      var container = document.querySelector('.page_title') || document.querySelector('.tab_cadre') || document.querySelector('#content') || document.body;
      // try to place near page title
      var title = container.querySelector('h1, h2, .title');
      if (title && title.parentNode) {
        title.parentNode.insertBefore(btn, title.nextSibling);
      } else if (container) {
        container.insertBefore(btn, container.firstChild);
      }
    } catch(e){
      console && console.log && console.log('generate_btn error', e);
    }
  });
})();
