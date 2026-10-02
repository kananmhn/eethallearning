// Building blocks shared by the directory app and the Enroll Now pages.
import { CONFIG } from "./core.js";
import { I } from "./icons.jsx";

const { useState, useEffect } = React;

// Eethal Learning logo: navy on white bars, white on the dark sidebar. Falls back to the name as text.
export function BrandLogo({onDark}){
  const src = CONFIG.logos && (onDark ? CONFIG.logos.onDark : CONFIG.logos.onLight);
  if(!src) return <div className={"logo-text font-display"+(onDark?" on-dark":"")}>Eethal Learning</div>;
  return <img className={"brand-logo"+(onDark?" brand-logo--dark":"")} src={src} alt="Eethal Learning" width="476" height="167"/>;
}

export const GRADIENTS = [["#6366F1","#EC4899"],["#0EA5E9","#8B5CF6"],["#0FA981","#5B5FEF"],["#F59E0B","#EF4444"],["#EC4899","#7C3AED"],["#10B981","#0891B2"]];

export function hashStr(s){ let h=0; for(let i=0;i<s.length;i++){h=(h*31+s.charCodeAt(i))|0;} return Math.abs(h); }
export function gradientFor(name){ const g=GRADIENTS[hashStr(name)%GRADIENTS.length]; return {background:`linear-gradient(135deg, ${g[0]}, ${g[1]})`}; }
export function initials(name){ return name.split(" ").filter(Boolean).slice(0,2).map(w=>w[0].toUpperCase()).join(""); }

// Filter helpers: values that differ only by spaces, dots or case are one option, so
// "B.E", "B. E" and "BE" merge, while "Bachelor of Engineering" stays separate.
export const tidy = v => String(v ?? "").trim().replace(/\s+/g," ");
export const compactKey = v => tidy(v).replace(/[\s.]/g,"").toLowerCase();
// Education is shown without a space after dots ("B. Tech" => "B.Tech"), as the server saves it.
export const tidyEducation = v => tidy(v).replace(/\.\s+/g,".");
export const sameText = (a,b) => compactKey(a) === compactKey(b);
export function uniqueOptions(values){
  const groups = new Map(); // compact key -> Map(spelling -> uses)
  values.forEach(v=>{
    const t = tidy(v); if(!t) return;
    const k = compactKey(t);
    if(!groups.has(k)) groups.set(k, new Map());
    groups.get(k).set(t, (groups.get(k).get(t)||0) + 1);
  });
  // Show each group's most used spelling.
  return [...groups.values()]
    .map(g=>[...g.entries()].sort((a,b)=>b[1]-a[1] || a[0].length-b[0].length)[0][0])
    .sort((a,b)=>a.localeCompare(b,undefined,{numeric:true,sensitivity:"base"}));
}

export function extractDriveId(url){
  if(!url || typeof url !== 'string') return null;
  const trimmed = url.trim();
  if(trimmed.startsWith('data:')) return null;
  if(trimmed.includes('drive.google.com') || trimmed.includes('googleusercontent.com')){
    const match = trimmed.match(/id=([a-zA-Z0-9_-]+)/) || trimmed.match(/\/d\/([a-zA-Z0-9_-]+)/);
    return match ? match[1] : null;
  }
  return null;
}

export function Avatar({name,size=44,gradient,photo}){
  const [imgError, setImgError] = useState(false);

  useEffect(()=>{
    setImgError(false);
  }, [photo]);

  const bg = gradient
    ? {background:`linear-gradient(135deg, ${gradient[0]}, ${gradient[1]})`}
    : gradientFor(name);

  let src = null;
  if(!imgError && photo && typeof photo === 'string' && photo.trim()){
    const trimmed = photo.trim();
    const driveId = extractDriveId(trimmed);
    if(driveId){
      src = `https://lh3.googleusercontent.com/d/${driveId}`;
    } else {
      src = trimmed;
    }
  }

  if(src){
    return (
      <img
        src={src}
        alt={name}
        className="avatar"
        style={{width:size,height:size,objectFit:'cover',objectPosition:'center top',borderRadius:'50%',boxShadow:'0 2px 8px rgba(0,0,0,0.1)',flexShrink:0}}
        onError={()=>setImgError(true)}
      />
    );
  }

  return (
    <div className="avatar" style={{width:size,height:size,fontSize:size*0.36, ...bg}}>
      {initials(name)}
    </div>
  );
}

export function EmptyState({title,subtitle,actionLabel,onAction}){
  return (
    <div className="empty-state">
      <I.Empty style={{color:'#C7CADC'}}/>
      <h3>{title}</h3>
      <p>{subtitle}</p>
      {actionLabel && <button className="btn btn-primary" onClick={onAction}><I.Plus/>{actionLabel}</button>}
    </div>
  );
}

export function FilterSelect({value,onChange,options,placeholder}){
  return (
    <select className="filter-select" value={value} onChange={e=>onChange(e.target.value)}>
      <option value="">{placeholder}</option>
      {options.map(o=><option key={o} value={o}>{o}</option>)}
    </select>
  );
}

export function DetailField({label,value}){
  return <div className="detail-field"><div className="label">{label}</div><div className="value">{value||"\u2014"}</div></div>;
}

// Defined outside ProfileForm: a component created during render remounts its inputs on every keystroke.
export function Field({errors,field,label,required,children,span}){
  return (
    <div className={"form-field"+(span?" full":"")+(errors[field]?" error":"")}>
      <label>{label} {required && <span className="req">*</span>}</label>
      {children}
      {errors[field] && <span className="err-text">{errors[field]}</span>}
    </div>
  );
}

export function formatDate(iso){
  if(!iso) return "";
  return new Date(iso).toLocaleString("en-IN",{day:"numeric",month:"short",year:"numeric",hour:"numeric",minute:"2-digit"});
}
