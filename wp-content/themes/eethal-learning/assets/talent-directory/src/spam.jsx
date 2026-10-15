// Spam protection for the public forms (Enroll Now and the Entry Form). The server
// (inc/spam-guard.php) does the actual checks; this adds the page's form token to each
// submission and shows the Cloudflare Turnstile "I'm not a robot" box when a site key
// is set in Customize → Eethal Front Page → Form Spam Protection.
import { CONFIG } from "./core.js";

const { useEffect, useRef } = React;
const SPAM = CONFIG.spam || {};

let turnstileScript = null;
function loadTurnstile(){
  if(window.turnstile) return Promise.resolve(window.turnstile);
  if(!turnstileScript) turnstileScript = new Promise((resolve,reject)=>{
    const s = document.createElement("script");
    s.src = "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
    s.async = true;
    s.onload = ()=>resolve(window.turnstile);
    s.onerror = reject;
    document.head.appendChild(s);
  });
  return turnstileScript;
}

// The robot check. Calls onToken with its answer ("" once it expires). Answers work
// only once, so bump resetKey after a failed submit to get a fresh one.
export function Captcha({onToken, resetKey}){
  const box = useRef(null);
  const widget = useRef(null);

  useEffect(()=>{
    if(!SPAM.turnstileKey) return;
    let gone = false;
    loadTurnstile().then(ts=>{
      if(gone || !box.current) return;
      widget.current = ts.render(box.current, {
        sitekey: SPAM.turnstileKey,
        size: "flexible",
        callback: onToken,
        "expired-callback": ()=>onToken(""),
        "error-callback": ()=>onToken(""),
      });
    }).catch(()=>{}); // Blocked or offline: the server's message asks for the check.
    return ()=>{
      gone = true;
      if(widget.current!=null && window.turnstile) window.turnstile.remove(widget.current);
      widget.current = null;
    };
  },[]);

  useEffect(()=>{
    if(resetKey && widget.current!=null && window.turnstile){
      window.turnstile.reset(widget.current);
      onToken("");
    }
  },[resetKey]);

  if(!SPAM.turnstileKey) return null;
  return <div className="captcha-box" ref={box}/>;
}

// Sent with every public form submission.
export const spamFields = captcha => ({formToken: SPAM.token || "", captcha: captcha || ""});
