// WordPress connection: settings passed in by inc/talent-directory.php (REST base,
// nonce, signed-in user, page URLs, Enroll Now text) and the REST helper.
export const CONFIG = window.EETHAL_TD || {restUrl:"/wp-json/eethal/v1/", nonce:"", view:"dashboard", pages:{}, homeUrl:"/", user:null};
let restNonce = CONFIG.nonce;

export function api(path, options={}){
  return fetch(CONFIG.restUrl + path, {
    ...options,
    credentials:"same-origin",
    headers:{"Content-Type":"application/json", "X-WP-Nonce":restNonce, ...(options.headers||{})},
  });
}

// After signing in, the REST nonce changes.
export function setRestNonce(nonce){ restNonce = nonce; }
