(function(){
  function inAppLink(href){
    return href && (href.startsWith('/app/') || href.startsWith(window.location.origin + '/app/'));
  }
  function normalizeHref(href){
    try{
      if(href.startsWith('http')) return new URL(href).pathname + new URL(href).search;
      return href;
    }catch(e){ return href; }
  }
  function routeMap(path){
    // Map WooCommerce canonical paths into app routes
    if(path.startsWith('/cart')) return '/app/cart';
    if(path.startsWith('/checkout')) return '/app/checkout';
    // Map product permalinks to /app/product/{slug}
    const m = path.match(/\/product\/([^\/\?\#]+)/);
    if(m) return '/app/product/' + m[1];
    return path;
  }

  async function loadIntoShell(path){
    const url = routeMap(path);
    const res = await fetch(url, { headers: { 'X-Mortify': '1' } });
    const html = await res.text();
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const newContent = doc.querySelector('.content') || doc.querySelector('.mortify-content');
    const newTop = doc.querySelector('.top') || doc.querySelector('.mortify-top');
    if(newContent){
      (document.querySelector('.content') || document.querySelector('.mortify-content')).innerHTML = newContent.innerHTML;
    }
    if(newTop){
      (document.querySelector('.top') || document.querySelector('.mortify-top')).innerHTML = newTop.innerHTML;
      document.title = newTop.textContent.trim();
    }
    history.pushState({}, '', url);
    // update active tabs
    document.querySelectorAll('.tabs a, .mortify-tabs a').forEach(a=>{
      const href = a.getAttribute('href');
      a.classList.toggle('active', href === url);
    });
  }

  document.addEventListener('click', function(e){
    const a = e.target.closest('a');
    if(!a) return;
    let href = a.getAttribute('href');
    if(!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
    href = normalizeHref(href);
    const mapped = routeMap(href);
    // Intercept app routes and Woo routes/products
    if(mapped.startsWith('/app/') || mapped.startsWith('/app/product/')){
      e.preventDefault();
      loadIntoShell(mapped);
      return;
    }
    if(href.startsWith('/product/') || href.startsWith('/cart') || href.startsWith('/checkout')){
      e.preventDefault();
      loadIntoShell(href);
      return;
    }
  });

  // Intercept forms inside app (Add to cart, cart update, checkout continue)
  document.addEventListener('submit', function(e){
    const form = e.target;
    if(!form) return;
    // Only intercept within app shell
    if(!(document.querySelector('.content') || document.querySelector('.mortify-content'))) return;

    e.preventDefault();
    const action = form.getAttribute('action') || window.location.pathname + window.location.search;
    const path = routeMap(normalizeHref(action));
    const data = new FormData(form);

    fetch(path, { method: 'POST', body: data, credentials: 'same-origin', headers: { 'X-Mortify': '1' } })
      .then(r=>r.text())
      .then(html=>{
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newContent = doc.querySelector('.content') || doc.querySelector('.mortify-content');
        const newTop = doc.querySelector('.top') || doc.querySelector('.mortify-top');
        if(newContent){
          (document.querySelector('.content') || document.querySelector('.mortify-content')).innerHTML = newContent.innerHTML;
        }
        if(newTop){
          (document.querySelector('.top') || document.querySelector('.mortify-top')).innerHTML = newTop.innerHTML;
          document.title = newTop.textContent.trim();
        }
        history.pushState({}, '', path);
      });
  });

  window.addEventListener('popstate', function(){
    // Safe fallback: reload to ensure server state
    location.reload();
  });
})();
