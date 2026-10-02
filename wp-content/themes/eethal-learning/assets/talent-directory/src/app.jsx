const { useState, useEffect, useMemo, useRef, useCallback } = React;

/* ============ WORDPRESS ============ */
// Set by inc/talent-directory.php: REST base, nonce, signed-in admin, page URLs.
const CONFIG = window.EETHAL_TD || {restUrl:"/wp-json/eethal/v1/", nonce:"", view:"dashboard", pages:{}, homeUrl:"/", user:null};
let restNonce = CONFIG.nonce;

function api(path, options={}){
  return fetch(CONFIG.restUrl + path, {
    ...options,
    credentials:"same-origin",
    headers:{"Content-Type":"application/json", "X-WP-Nonce":restNonce, ...(options.headers||{})},
  });
}

function viewFromPath(pathname){
  const clean = p => p.replace(/\/+$/,"");
  const match = Object.keys(CONFIG.pages).find(k => clean(new URL(CONFIG.pages[k]).pathname) === clean(pathname));
  return match || null;
}

/* ============ ICONS ============ */
const I = {
  Home:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/></svg>),
  Users:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><circle cx="9" cy="8" r="3.2"/><path d="M2.5 20c.6-3.4 3.2-5.5 6.5-5.5s5.9 2.1 6.5 5.5"/><circle cx="17.5" cy="8.5" r="2.4"/><path d="M16 14.6c2.7.4 4.7 2.2 5.2 5"/></svg>),
  Grad:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M2 8 12 3l10 5-10 5-10-5Z"/><path d="M6 10.5V16c0 1.6 2.7 3 6 3s6-1.4 6-3v-5.5"/><path d="M21 8v6"/></svg>),
  Shield:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 3l8 3v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6l8-3Z"/><path d="M9 12l2 2 4-4"/></svg>),
  Chart:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M4 20V10"/><path d="M11 20V4"/><path d="M18 20v-7"/></svg>),
  Settings:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><circle cx="12" cy="12" r="3"/><path d="M19.4 13.5a7.6 7.6 0 0 0 0-3l1.9-1.4-2-3.4-2.2.8a7.6 7.6 0 0 0-2.6-1.5L14 2.5h-4l-.5 2.5a7.6 7.6 0 0 0-2.6 1.5l-2.2-.8-2 3.4L4.6 10.5a7.6 7.6 0 0 0 0 3L2.7 15l2 3.4 2.2-.9c.75.66 1.63 1.17 2.6 1.5l.5 2.5h4l.5-2.5a7.6 7.6 0 0 0 2.6-1.5l2.2.9 2-3.4-1.9-1.5Z"/></svg>),
  Logout:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>),
  Search:(p)=>(<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>),
  Bell:(p)=>(<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M18 8a6 6 0 1 0-12 0c0 3.5-1 5-2 6h16c-1-1-2-2.5-2-6Z"/><path d="M9.5 20a2.5 2.5 0 0 0 5 0"/></svg>),
  Chevron:(p)=>(<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="m6 9 6 6 6-6"/></svg>),
  Dots:(p)=>(<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" {...p}><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>),
  Back:(p)=>(<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="m12 19-7-7 7-7"/><path d="M5 12h14"/></svg>),
  Pin:(p)=>(<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 21s-7-6.1-7-11a7 7 0 1 1 14 0c0 4.9-7 11-7 11Z"/><circle cx="12" cy="10" r="2.4"/></svg>),
  Mail:(p)=>(<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>),
  Phone:(p)=>(<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M6.6 10.8a13 13 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 10 10 0 0 0 3.1.5 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 10 10 0 0 0 .5 3.1 1 1 0 0 1-.25 1L6.6 10.8Z"/></svg>),
  Building:(p)=>(<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 8h1M14 8h1M9 12h1M14 12h1M9 16h1M14 16h1"/></svg>),
  Plus:(p)=>(<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 5v14M5 12h14"/></svg>),
  Edit:(p)=>(<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>),
  Trash:(p)=>(<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/></svg>),
  Eye:(p)=>(<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>),
  Check:(p)=>(<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M4 12l5 5L20 6"/></svg>),
  CheckCircle:(p)=>(<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" {...p}><circle cx="12" cy="12" r="10"/><path d="M8.5 12.5l2.3 2.3 5-5" stroke="#fff" strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>),
  Close:(p)=>(<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M18 6 6 18M6 6l12 12"/></svg>),
  Menu:(p)=>(<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M4 6h16M4 12h16M4 18h16"/></svg>),
  Alert:(p)=>(<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 9v4"/><path d="M10.3 3.9 2.5 17.5A1.5 1.5 0 0 0 3.8 20h16.4a1.5 1.5 0 0 0 1.3-2.5L13.7 3.9a1.5 1.5 0 0 0-2.6 0Z"/><path d="M12 16.2h.01"/></svg>),
  Filter:(p)=>(<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M4 5h16M7 12h10M10 19h4"/></svg>),
  Empty:(p)=>(<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round" {...p}><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/><path d="M8.5 11h5"/></svg>),
  Rupee:(p)=>(<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M6 4h12M6 9h12M9 4c3.3 0 5.5 2 5.5 5S12.3 14 9 14H7l7.5 7"/></svg>),
  Heart:(p)=>(<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 20s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7a4.3 4.3 0 0 1 7.5 2.8C19.5 15.4 12 20 12 20Z"/></svg>),
  Arrow:(p)=>(<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M5 12h14M13 6l6 6-6 6"/></svg>),
};

/* ============ MOCK DATA ============ */
const DISTRICTS = ["Chennai","Coimbatore","Madurai","Trichy","Salem","Erode","Vellore","Tirunelveli"];
const DESIGNATIONS = ["Software Engineer","Senior Software Engineer","Product Manager","Data Analyst","UX Designer","Business Analyst","DevOps Engineer","HR Manager"];
const COMPANIES = ["Cognizant","TCS","Infosys","Wipro","Zoho","Freshworks","HCLTech","Accenture"];
const EDUCATIONS = ["B.E","B.Tech","M.E","M.Tech","MBA","MCA","B.Sc","M.Sc"];
const SPECIALIZATIONS = ["Computer Science Engineering","Information Technology","Electronics & Communication","Mechanical Engineering","Data Science","AI & Machine Learning","Business Administration","Commerce"];
const GRAD_YEARS = [2023,2024,2025,2026,2027];
const MARITAL_STATUSES = ["Married","Unmarried"];
const GRADIENTS = [["#6366F1","#EC4899"],["#0EA5E9","#8B5CF6"],["#0FA981","#5B5FEF"],["#F59E0B","#EF4444"],["#EC4899","#7C3AED"],["#10B981","#0891B2"]];
const ADMIN_GRADIENT = ["#5B5FEF","#8B5CF6"];

function hashStr(s){ let h=0; for(let i=0;i<s.length;i++){h=(h*31+s.charCodeAt(i))|0;} return Math.abs(h); }
function gradientFor(name){ const g=GRADIENTS[hashStr(name)%GRADIENTS.length]; return {background:`linear-gradient(135deg, ${g[0]}, ${g[1]})`}; }
function initials(name){ return name.split(" ").filter(Boolean).slice(0,2).map(w=>w[0].toUpperCase()).join(""); }
function pick(arr,seed){ return arr[seed % arr.length]; }

// Filter helpers: values that differ only by spaces, dots or case are one option, so
// "B.E", "B. E" and "BE" merge, while "Bachelor of Engineering" stays separate.
const tidy = v => String(v ?? "").trim().replace(/\s+/g," ");
const compactKey = v => tidy(v).replace(/[\s.]/g,"").toLowerCase();
// Education is shown without a space after dots ("B. Tech" => "B.Tech"), as the server saves it.
const tidyEducation = v => tidy(v).replace(/\.\s+/g,".");
const sameText = (a,b) => compactKey(a) === compactKey(b);
function uniqueOptions(values){
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

const NOTIFICATIONS = [
  {id:1,text:"New professional \u201cKarthik R\u201d was added to the directory.",time:"10 min ago",unread:true},
  {id:2,text:"Student profile \u201cHarini P\u201d was updated.",time:"1 hour ago",unread:true},
  {id:3,text:"Admin profile \u201cPreethi Vasan\u201d changed role to Editor.",time:"3 hours ago",unread:true},
  {id:4,text:"Profile \u201cRohan Verma\u201d was deleted from Students.",time:"Yesterday",unread:false},
  {id:5,text:"New professional \u201cMeena Rajan\u201d was added to the directory.",time:"2 days ago",unread:false},
];

/* ============ SMALL COMPONENTS ============ */
function extractDriveId(url){
  if(!url || typeof url !== 'string') return null;
  const trimmed = url.trim();
  if(trimmed.startsWith('data:')) return null;
  if(trimmed.includes('drive.google.com') || trimmed.includes('googleusercontent.com')){
    const match = trimmed.match(/id=([a-zA-Z0-9_-]+)/) || trimmed.match(/\/d\/([a-zA-Z0-9_-]+)/);
    return match ? match[1] : null;
  }
  return null;
}

function Avatar({name,size=44,gradient,photo}){
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

function Toast({message,onClose}){
  useEffect(()=>{ const t=setTimeout(onClose,2600); return ()=>clearTimeout(t); },[message]);
  if(!message) return null;
  return (
    <div className="toast"><I.CheckCircle style={{color:'#12B76A',flexShrink:0}}/> {message}</div>
  );
}

function useOutsideClose(ref,onClose){
  useEffect(()=>{
    function handler(e){ if(ref.current && !ref.current.contains(e.target)) onClose(); }
    document.addEventListener("mousedown",handler);
    return ()=>document.removeEventListener("mousedown",handler);
  },[ref,onClose]);
}

function CardMenu({onView,onEdit,onDelete,isAdmin}){
  const [open,setOpen]=useState(false);
  const ref=useRef(null);
  useOutsideClose(ref,()=>setOpen(false));
  if(!isAdmin) return null;
  return (
    <div style={{position:'relative'}} ref={ref} onClick={e=>e.stopPropagation()}>
      <button className="dots-btn" onClick={()=>setOpen(o=>!o)}><I.Dots/></button>
      {open && (
        <div className="dropdown-menu" style={{right:0,top:30}}>
          <button onClick={()=>{setOpen(false);onView();}}><I.Eye/> View Profile</button>
          <button onClick={()=>{setOpen(false);onEdit();}}><I.Edit/> Edit</button>
          <button className="danger" onClick={()=>{setOpen(false);onDelete();}}><I.Trash/> Delete</button>
        </div>
      )}
    </div>
  );
}

function EmptyState({title,subtitle,actionLabel,onAction}){
  return (
    <div className="empty-state">
      <I.Empty style={{color:'#C7CADC'}}/>
      <h3>{title}</h3>
      <p>{subtitle}</p>
      {actionLabel && <button className="btn btn-primary" onClick={onAction}><I.Plus/>{actionLabel}</button>}
    </div>
  );
}

// "Load More": `page` is how many batches are shown, so each click appends the
// next `pageSize` profiles below the ones already on screen.
function LoadMore({page,setPage,total,pageSize}){
  const totalPages = Math.max(1,Math.ceil(total/pageSize));
  // Deleting profiles can leave more batches loaded than exist.
  useEffect(()=>{ if(page>totalPages) setPage(totalPages); },[page,totalPages]);
  if(total===0) return null;
  const shown = Math.min(total,page*pageSize);
  const remaining = total-shown;
  return (
    <div className="load-more">
      <div className="load-more-info">Showing <strong>{shown}</strong> of <strong>{total}</strong> profiles</div>
      <div className="load-more-bar"><span style={{width:`${(shown/total)*100}%`}}/></div>
      {remaining>0
        ? <button className="btn btn-primary load-more-btn" onClick={()=>setPage(p=>p+1)}>Load More ({Math.min(pageSize,remaining)})</button>
        : total>pageSize && <div className="load-more-end">You've reached the end of the list</div>}
    </div>
  );
}

/* ============ TOP NAVIGATION (logo + subnav + header) ============ */
/* ============ ADMIN SIDEBAR (only shown once signed in) ============ */
function Sidebar({view,setView,onLogout}){
  const items = [
    {key:"dashboard",label:"Dashboard",icon:I.Home},
    {key:"professionals",label:"Working Professionals",icon:I.Users},
    {key:"students",label:"Students",icon:I.Grad},
  ];
  return (
    <aside className="sidebar">
      <div className="sidebar-logo-row">
        <div className="logo-mark">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><circle cx="7" cy="7" r="2.6"/><circle cx="17" cy="7" r="2.6"/><circle cx="12" cy="17" r="2.6"/><path d="M9 8.5 10.3 15M15 8.5 13.7 15"/></svg>
        </div>
        <div className="logo-text on-dark font-display">Eethal Learning</div>
      </div>
      <div className="sidebar-badge"><I.Shield style={{width:14,height:14}}/> Admin Mode</div>
      <nav className="nav-section">
        {items.map(it=>(
          <button key={it.key} className={"nav-item"+(view===it.key?" active":"")} onClick={()=>setView(it.key)}>
            <it.icon/> {it.label}
          </button>
        ))}
        <div className="nav-divider"/>
        <button className="nav-item" onClick={onLogout}><I.Logout/> Logout</button>
      </nav>
    </aside>
  );
}

function TopHeader({view, setView, globalSearch, setGlobalSearch, onSearchSubmit, notifOpen, setNotifOpen, profileOpen, setProfileOpen, notifications, isAdmin, adminUser, onRequestLogin, onLogout}){
  const notifRef = useRef(null);
  const profRef = useRef(null);
  useOutsideClose(notifRef, ()=>setNotifOpen(false));
  useOutsideClose(profRef, ()=>setProfileOpen(false));
  const unread = notifications.filter(n=>n.unread).length;

  const navItems = [
    {key:"dashboard",label:"Dashboard",icon:I.Home},
    {key:"professionals",label:"Working Professionals",icon:I.Users},
    {key:"students",label:"Students",icon:I.Grad},
  ];

  function handleChipClick(){
    setNotifOpen(false);
    if(isAdmin){ setProfileOpen(o=>!o); }
    else { onRequestLogin(); }
  }

  return (
    <div className="header-shell">
      <header className="topbar">
        {!isAdmin && (
          <a className="brand-row" href={CONFIG.homeUrl}>
            <div className="logo-mark">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><circle cx="7" cy="7" r="2.6"/><circle cx="17" cy="7" r="2.6"/><circle cx="12" cy="17" r="2.6"/><path d="M9 8.5 10.3 15M15 8.5 13.7 15"/></svg>
            </div>
            <div className="logo-text font-display">Eethal Learning</div>
          </a>
        )}
        <div className="search-box">
          <button type="button" className="search-go" onClick={onSearchSubmit} aria-label="Search">
            <I.Search/>
          </button>
          <input id="global-search" aria-label="Search profiles" placeholder="Search name, company, district, CTC..." value={globalSearch}
            onChange={e=>setGlobalSearch(e.target.value)}
            onKeyDown={e=>{ if(e.key==='Enter') onSearchSubmit(); if(e.key==='Escape') setGlobalSearch(""); }}/>
          {globalSearch && (
            <button type="button" className="search-clear" onClick={()=>setGlobalSearch("")} aria-label="Clear search">
              <I.Close/>
            </button>
          )}
        </div>
        <div className="topbar-right">
          <div ref={notifRef} style={{position:'relative'}}>
            <button className="icon-btn" onClick={()=>{setNotifOpen(o=>!o);setProfileOpen(false);}}>
              <I.Bell/>{unread>0 && <span className="badge-dot"/>}
            </button>
            {notifOpen && (
              <div className="notif-panel">
                <div className="notif-head">Notifications</div>
                {notifications.map(n=>(
                  <div key={n.id} className={"notif-item"+(n.unread?"":" read")}>
                    <div className="notif-dot"/>
                    <div><div className="notif-text">{n.text}</div><div className="notif-time">{n.time}</div></div>
                  </div>
                ))}
              </div>
            )}
          </div>
          <div ref={profRef} style={{position:'relative'}}>
            <button className="admin-chip" onClick={handleChipClick}>
              <Avatar name={isAdmin ? (adminUser?.name || "Admin") : "Guest Viewer"} size={30} gradient={isAdmin?ADMIN_GRADIENT:undefined}/>
              <span style={{fontSize:13,fontWeight:600}}>{isAdmin ? (adminUser?.name?.split(" ")[0] || "Admin") : "Sign In"}</span>
              <I.Chevron/>
            </button>
            {profileOpen && isAdmin && (
              <div className="dropdown-menu" style={{right:0,top:50,minWidth:190}}>
                <button onClick={()=>setView('profile')}><I.Users style={{width:14,height:14}}/> Profile</button>
                <div className="dropdown-divider"/>
                <button className="danger" onClick={onLogout}><I.Logout style={{width:14,height:14}}/> Logout</button>
              </div>
            )}
          </div>
        </div>
      </header>
      {!isAdmin && (
        <nav className="subnav">
          {navItems.map(it=>(
            <button key={it.key} className={"subnav-item"+(view===it.key?" active":"")} onClick={()=>setView(it.key)}>
              <it.icon/> {it.label}
            </button>
          ))}
        </nav>
      )}
    </div>
  );
}

function MobileBottomNav({view,setView}){
  const items=[{key:"dashboard",label:"Home",icon:I.Home},{key:"professionals",label:"Professionals",icon:I.Users},{key:"students",label:"Students",icon:I.Grad}];
  return (
    <nav className="mobile-bottom-nav">
      {items.map(it=>(
        <button key={it.key} className={view===it.key?"active":""} onClick={()=>setView(it.key)}>
          <it.icon/> {it.label}
        </button>
      ))}
    </nav>
  );
}

/* ============ DASHBOARD ============ */
function StatCard({icon,color,bg,value,label}){
  return (
    <div className="stat-card">
      <div className="stat-top">
        <div className="stat-icon" style={{background:bg,color:color}}>{icon}</div>
      </div>
      <div className="stat-value">{value}</div>
      <div className="stat-label">{label}</div>
    </div>
  );
}

function Dashboard({professionals,students,setView,openDetail}){
  // Three of each fills one row of the 3-column card grid.
  const recentPros = [...professionals].sort((a,b)=>new Date(b.createdAt)-new Date(a.createdAt)).slice(0,3);
  const recentStu = [...students].sort((a,b)=>new Date(b.createdAt)-new Date(a.createdAt)).slice(0,3);
  return (
    <div>
      <div className="page-head">
        <div><h1 className="font-display">Dashboard</h1><p>Manage and discover professionals and students.</p></div>
      </div>
      <div className="stat-grid">
        <StatCard icon={<I.Users/>} color="#5B5FEF" bg="#EEF0FF" value={professionals.length} label="Working Professionals"/>
        <StatCard icon={<I.Grad/>} color="#0FA981" bg="#E4F7F1" value={students.length} label="Students"/>
      </div>

      <div className="section-card">
        <div className="section-head">
          <h3>Recent Professionals</h3>
          <button className="link-btn" onClick={()=>setView("professionals")}>View All</button>
        </div>
        <div className="card-grid">
          {recentPros.map(p=>(
            <ProfessionalCard key={p.id} p={p} onClick={()=>openDetail("professional",p.id)}/>
          ))}
        </div>
      </div>

      <div className="section-card">
        <div className="section-head">
          <h3>Recent Students</h3>
          <button className="link-btn" onClick={()=>setView("students")}>View All</button>
        </div>
        <div className="card-grid">
          {recentStu.map(s=>(
            <StudentCard key={s.id} s={s} onClick={()=>openDetail("student",s.id)}/>
          ))}
        </div>
      </div>
    </div>
  );
}

/* ============ PROFILE CARD (ID badge) ============ */
// Shared by the Dashboard, the directory pages and search results. The band
// colour follows the avatar gradient for the name; facts without a value are skipped.
function BadgeCard({name,photo,verified,tag,headline,headlineText,facts,district,onClick,onEdit,onDelete,isAdmin}){
  const g = GRADIENTS[hashStr(name)%GRADIENTS.length];
  const onKey = e=>{ if(e.target===e.currentTarget && (e.key==="Enter"||e.key===" ")){ e.preventDefault(); onClick(); } };
  const shown = facts.filter(f=>f.value);
  return (
    <div className="profile-card id-card" onClick={onClick} onKeyDown={onKey} tabIndex={0}>
      <div className="id-band" style={{background:`linear-gradient(120deg, ${g[0]}33, ${g[1]}26)`}}>
        {tag && <span className="id-tag">{tag}</span>}
        <CardMenu onView={onClick} onEdit={onEdit} onDelete={onDelete} isAdmin={isAdmin}/>
      </div>
      <div className="id-body">
        <div className="id-avatar"><Avatar name={name} size={120} photo={photo}/></div>
        <div className="pc-name id-name">{name} {verified && <I.CheckCircle style={{color:'#0FA981',width:15,height:15}} aria-label="Verified"/>}</div>
        <div className="id-headline" title={headlineText}>{headline}</div>
        <div className="id-facts">
          {shown.map(f=>(
            <div key={f.label} className={`id-fact id-fact--${f.kind||"default"}${f.wide?" is-wide":""}${f.value==="NA"?" is-na":""}`} title={`${f.label}: ${f.value}`}>
              <span className="id-fact-ic">{f.icon}</span>
              <span className="id-fact-tx">
                <span className="id-fact-lbl">{f.label}</span>
                <span className="id-fact-val">{f.value}</span>
              </span>
            </div>
          ))}
        </div>
        <div className="id-foot">
          <span className="pc-meta"><I.Pin/> {district}</span>
          <span className="id-view">View <I.Arrow/></span>
        </div>
      </div>
    </div>
  );
}

/* ============ PROFESSIONALS LIST ============ */
function ProfessionalCard({p,onClick,onEdit,onDelete,isAdmin}){
  const headline = p.designation && p.company
    ? <><b>{p.designation}</b> at {p.company}</>
    : <b>{p.designation||p.company}</b>;
  return (
    <BadgeCard
      name={p.name} photo={p.photo} verified={p.verified} district={p.district}
      tag={p.experience ? `${p.experience} yrs exp` : ""}
      headline={headline} headlineText={[p.designation,p.company].filter(Boolean).join(" at ")}
      facts={[
        {label:"Current CTC", kind:"ctc", icon:<I.Rupee/>, value:p.ctc},
        {label:"Marital status", kind:"marital", icon:<I.Heart/>, value:p.marital||"NA"},
        {label:"Education", kind:"edu", wide:true, icon:<I.Grad/>, value:p.education||"NA"},
      ]}
      onClick={onClick} onEdit={onEdit} onDelete={onDelete} isAdmin={isAdmin}
    />
  );
}

function FilterSelect({value,onChange,options,placeholder}){
  return (
    <select className="filter-select" value={value} onChange={e=>onChange(e.target.value)}>
      <option value="">{placeholder}</option>
      {options.map(o=><option key={o} value={o}>{o}</option>)}
    </select>
  );
}

function ProfessionalsPage({professionals,openDetail,openAdd,openEdit,openDelete,isAdmin}){
  const [search,setSearch]=useState("");
  const [f,setF]=useState({district:"",experience:"",designation:"",company:"",education:""});
  const [page,setPage]=useState(1);
  const pageSize=10;

  // Dynamic filter options derived from actual data
  const filterOpts = useMemo(()=>({
    districts: uniqueOptions(professionals.map(p=>p.district)),
    designations: uniqueOptions(professionals.map(p=>p.designation)),
    companies: uniqueOptions(professionals.map(p=>p.company)),
    educations: uniqueOptions(professionals.map(p=>tidyEducation(p.education))),
  }),[professionals]);

  const filtered = useMemo(()=>{
    return professionals.filter(p=>{
      const s = search.toLowerCase();
      const matchesSearch = !s || p.name.toLowerCase().includes(s) || p.company.toLowerCase().includes(s) || p.designation.toLowerCase().includes(s) || p.district.toLowerCase().includes(s);
      const matchesDistrict = !f.district || sameText(p.district,f.district);
      const matchesDesig = !f.designation || sameText(p.designation,f.designation);
      const matchesCompany = !f.company || sameText(p.company,f.company);
      const matchesEdu = !f.education || sameText(p.education,f.education);
      const exp = parseFloat(p.experience) || 0;
      const matchesExp = !f.experience || (
        f.experience==="0-2" ? exp<2 :
        f.experience==="2-5" ? (exp>=2 && exp<5) :
        f.experience==="5-8" ? (exp>=5 && exp<8) : exp>=8
      );
      return matchesSearch && matchesDistrict && matchesDesig && matchesCompany && matchesEdu && matchesExp;
    });
  },[professionals,search,f]);

  const pageItems = filtered.slice(0,page*pageSize);
  const resetFilters=()=>{setSearch("");setF({district:"",experience:"",designation:"",company:"",education:""});setPage(1);};
  const hasNoData = professionals.length===0;

  return (
    <div>
      <div className="page-head">
        <div><h1 className="font-display">Working Professionals</h1><p>Discover and manage experienced professionals.</p></div>
        {isAdmin && <button className="btn btn-primary" onClick={openAdd}><I.Plus/> Add Professional</button>}
      </div>

      <div className="filter-bar">
        <div className="filter-search">
          <I.Search/>
          <input placeholder="Search by name, company, designation..." value={search} onChange={e=>{setSearch(e.target.value);setPage(1);}}/>
        </div>
        <div className="filter-row">
          <FilterSelect value={f.district} onChange={v=>{setF({...f,district:v});setPage(1);}} options={filterOpts.districts} placeholder="Current District"/>
          <FilterSelect value={f.experience} onChange={v=>{setF({...f,experience:v});setPage(1);}} options={["0-2","2-5","5-8","8+"]} placeholder="Experience"/>
          <FilterSelect value={f.designation} onChange={v=>{setF({...f,designation:v});setPage(1);}} options={filterOpts.designations} placeholder="Designation"/>
          <FilterSelect value={f.company} onChange={v=>{setF({...f,company:v});setPage(1);}} options={filterOpts.companies} placeholder="Company"/>
          <FilterSelect value={f.education} onChange={v=>{setF({...f,education:v});setPage(1);}} options={filterOpts.educations} placeholder="Education"/>
          <button className="btn btn-secondary btn-sm" onClick={resetFilters}>Reset</button>
        </div>
      </div>

      {hasNoData ? (
        <EmptyState title="No Professionals Added Yet" subtitle="Start building your talent directory by adding your first professional." actionLabel={isAdmin?"Add Professional":undefined} onAction={openAdd}/>
      ) : filtered.length===0 ? (
        <EmptyState title="No profiles found" subtitle="Try changing your search or filters." actionLabel="Clear Filters" onAction={resetFilters}/>
      ) : (
        <>
        <div className="card-grid">
          {pageItems.map(p=>(
            <ProfessionalCard key={p.id} p={p} onClick={()=>openDetail("professional",p.id)} onEdit={()=>openEdit("professional",p)} onDelete={()=>openDelete("professional",p)} isAdmin={isAdmin}/>
          ))}
        </div>
        <LoadMore page={page} setPage={setPage} total={filtered.length} pageSize={pageSize}/>
        </>
      )}
    </div>
  );
}

/* ============ STUDENTS LIST ============ */
function StudentCard({s,onClick,onEdit,onDelete,isAdmin}){
  return (
    <BadgeCard
      name={s.name} photo={s.photo} district={s.district}
      tag={s.gradYear ? `Batch ${s.gradYear}` : ""}
      headline={<b>{s.specialization}</b>}
      headlineText={s.specialization}
      facts={[
        {label:"Marital status", kind:"marital", wide:true, icon:<I.Heart/>, value:s.marital||"NA"},
        {label:"Education", kind:"edu", wide:true, icon:<I.Grad/>, value:s.education||"NA"},
      ]}
      onClick={onClick} onEdit={onEdit} onDelete={onDelete} isAdmin={isAdmin}
    />
  );
}

function StudentsPage({students,openDetail,openAdd,openEdit,openDelete,isAdmin}){
  const [search,setSearch]=useState("");
  const [f,setF]=useState({gradYear:"",specialization:"",district:"",education:""});
  const [page,setPage]=useState(1);
  const pageSize=10;

  // Dynamic filter options derived from actual data
  const filterOpts = useMemo(()=>({
    gradYears: uniqueOptions(students.map(s=>s.gradYear)),
    specializations: uniqueOptions(students.map(s=>s.specialization)),
    districts: uniqueOptions(students.map(s=>s.district)),
    educations: uniqueOptions(students.map(s=>tidyEducation(s.education))),
  }),[students]);

  const filtered = useMemo(()=>{
    return students.filter(s=>{
      const q = search.toLowerCase();
      const matchesSearch = !q || s.name.toLowerCase().includes(q) || s.specialization.toLowerCase().includes(q) || s.district.toLowerCase().includes(q);
      const matchesYear = !f.gradYear || sameText(s.gradYear,f.gradYear);
      const matchesSpec = !f.specialization || sameText(s.specialization,f.specialization);
      const matchesDistrict = !f.district || sameText(s.district,f.district);
      const matchesEdu = !f.education || sameText(s.education,f.education);
      return matchesSearch && matchesYear && matchesSpec && matchesDistrict && matchesEdu;
    });
  },[students,search,f]);

  const pageItems = filtered.slice(0,page*pageSize);
  const resetFilters=()=>{setSearch("");setF({gradYear:"",specialization:"",district:"",education:""});setPage(1);};
  const hasNoData = students.length===0;

  return (
    <div>
      <div className="page-head">
        <div><h1 className="font-display">Students</h1><p>Discover and connect with talented students.</p></div>
        {isAdmin && <button className="btn btn-primary" onClick={openAdd}><I.Plus/> Add Student</button>}
      </div>

      <div className="filter-bar">
        <div className="filter-search">
          <I.Search/>
          <input placeholder="Search by name, college, specialization..." value={search} onChange={e=>{setSearch(e.target.value);setPage(1);}}/>
        </div>
        <div className="filter-row">
          <FilterSelect value={f.gradYear} onChange={v=>{setF({...f,gradYear:v});setPage(1);}} options={filterOpts.gradYears} placeholder="Year of Graduation"/>
          <FilterSelect value={f.specialization} onChange={v=>{setF({...f,specialization:v});setPage(1);}} options={filterOpts.specializations} placeholder="Specialization"/>
          <FilterSelect value={f.district} onChange={v=>{setF({...f,district:v});setPage(1);}} options={filterOpts.districts} placeholder="Current District"/>
          <FilterSelect value={f.education} onChange={v=>{setF({...f,education:v});setPage(1);}} options={filterOpts.educations} placeholder="Education"/>
          <button className="btn btn-secondary btn-sm" onClick={resetFilters}>Reset</button>
        </div>
      </div>

      {hasNoData ? (
        <EmptyState title="No Students Added Yet" subtitle="Start building your talent directory by adding your first student." actionLabel={isAdmin?"Add Student":undefined} onAction={openAdd}/>
      ) : filtered.length===0 ? (
        <EmptyState title="No profiles found" subtitle="Try changing your search or filters." actionLabel="Clear Filters" onAction={resetFilters}/>
      ) : (
        <>
        <div className="card-grid">
          {pageItems.map(s=>(
            <StudentCard key={s.id} s={s} onClick={()=>openDetail("student",s.id)} onEdit={()=>openEdit("student",s)} onDelete={()=>openDelete("student",s)} isAdmin={isAdmin}/>
          ))}
        </div>
        <LoadMore page={page} setPage={setPage} total={filtered.length} pageSize={pageSize}/>
        </>
      )}
    </div>
  );
}

/* ============ GLOBAL SEARCH RESULTS ============ */
const SEARCH_FIELDS = {
  professional: ["name","company","designation","district","education","ctc","experience","email","mobile"],
  student: ["name","specialization","education","district","gradYear","email","mobile"],
};

// Every word must match some field, so "associate madurai" finds Associates in
// Madurai. Marital status matches whole words only: "married" must not match "Unmarried".
function matchesSearch(item,fields,terms){
  const values = fields.map(f=>String(item[f]||"").toLowerCase());
  const marital = String(item.marital||"").toLowerCase();
  return terms.every(t=> marital===t || values.some(v=>v.includes(t)));
}

function SearchResultsPage({query,professionals,students,openDetail,openEdit,openDelete,isAdmin}){
  const q = query.trim().toLowerCase();
  const terms = q.split(/\s+/).filter(Boolean);

  const matchedPros = !q ? [] : professionals.filter(p=>matchesSearch(p,SEARCH_FIELDS.professional,terms));
  const matchedStus = !q ? [] : students.filter(s=>matchesSearch(s,SEARCH_FIELDS.student,terms));
  const total = matchedPros.length + matchedStus.length;

  return (
    <div>
      <div className="page-head">
        <div>
          <h1 className="font-display">Search Results</h1>
          <p>{q ? <>Showing results for &ldquo;{query.trim()}&rdquo; &mdash; {total} profile{total===1?"":"s"} found.</> : "Type in the search bar above to find people by name, company, designation, specialization, education, or district."}</p>
        </div>
      </div>

      {q && total===0 && (
        <EmptyState title="No profiles found" subtitle="Try changing your search terms or check for typos."/>
      )}

      {matchedPros.length>0 && (
        <div className="section-card">
          <div className="section-head"><h3>Working Professionals ({matchedPros.length})</h3></div>
          <div className="card-grid">
            {matchedPros.map(p=>(
              <ProfessionalCard key={p.id} p={p} onClick={()=>openDetail("professional",p.id)} onEdit={()=>openEdit("professional",p)} onDelete={()=>openDelete("professional",p)} isAdmin={isAdmin}/>
            ))}
          </div>
        </div>
      )}

      {matchedStus.length>0 && (
        <div className="section-card">
          <div className="section-head"><h3>Students ({matchedStus.length})</h3></div>
          <div className="card-grid">
            {matchedStus.map(s=>(
              <StudentCard key={s.id} s={s} onClick={()=>openDetail("student",s.id)} onEdit={()=>openEdit("student",s)} onDelete={()=>openDelete("student",s)} isAdmin={isAdmin}/>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

/* ============ PROFILE DETAIL ============ */
function DetailField({label,value}){
  return <div className="detail-field"><div className="label">{label}</div><div className="value">{value||"\u2014"}</div></div>;
}

function ProfileDetailPage({type,data,onBack,onEdit,onDelete,isAdmin}){
  const isP = type==="professional";
  return (
    <div>
      <div className="detail-top">
        <button className="back-btn" onClick={onBack}><I.Back/> Back to List</button>
        {isAdmin ? (
          <div style={{display:'flex',gap:8}}>
            <button className="btn btn-secondary btn-sm" onClick={onEdit}><I.Edit/> Edit</button>
            <button className="btn btn-secondary btn-sm" onClick={onDelete} style={{color:'var(--red)'}}><I.Trash/> Delete</button>
          </div>
        ) : (
          <span className="role-tag" style={{fontSize:11.5,fontWeight:700,color:'var(--text-faint)',border:'1px solid var(--border)',borderRadius:999,padding:'6px 12px'}}>View only</span>
        )}
      </div>

      <div className="profile-header-card">
        <Avatar name={data.name} size={120} photo={data.photo}/>
        <div>
          <div className="ph-name-row">
            <h2 className="font-display">{data.name}</h2>
            {isP && data.verified && <I.CheckCircle style={{color:'#0FA981'}}/>}
          </div>
          <div className="ph-role">{isP ? `${data.designation} | ${data.company}` : `${data.specialization} \u00b7 ${data.education}`}</div>
          <div className="ph-contacts">
            <div className="ph-contact-item"><I.Pin/> {data.district}, Tamil Nadu</div>
            <div className="ph-contact-item"><I.Mail/> {data.email}</div>
            <div className="ph-contact-item"><I.Phone/> +91 {data.mobile}</div>
          </div>
        </div>
      </div>

      <div className="section-card">
        <div className="section-head"><h3>{isP ? "Professional Details" : "Student Details"}</h3></div>
        <div className="detail-grid">
          <div>
            <DetailField label="Full Name" value={data.name}/>
            <DetailField label="Mobile Number" value={"+91 "+data.mobile}/>
            <DetailField label="Email ID" value={data.email}/>
            <DetailField label="Current District" value={data.district}/>
            {isP ? (
              <>
                <DetailField label="Total Years of Experience" value={data.experience+" years"}/>
              </>
            ) : (
              <>
                <DetailField label="Specialization" value={data.specialization}/>
              </>
            )}
            <DetailField label="Marital Status" value={data.marital}/>
          </div>
          <div>
            {isP ? (
              <>
                <DetailField label="Current Company" value={data.company}/>
                <DetailField label="Current Designation" value={data.designation}/>
                <DetailField label="Current CTC" value={data.ctc}/>
                <DetailField label="Education Qualification" value={data.education}/>
                <DetailField label="Current Address" value={data.address}/>
              </>
            ) : (
              <>
                <DetailField label="Year of Graduation" value={data.gradYear}/>
                <DetailField label="Education Qualification" value={data.education}/>
                <DetailField label="Current Address" value={data.address}/>
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

/* ============ ADD / EDIT FORM ============ */
// Defined outside ProfileForm: a component created during render remounts its inputs on every keystroke.
function Field({errors,field,label,required,children,span}){
  return (
    <div className={"form-field"+(span?" full":"")+(errors[field]?" error":"")}>
      <label>{label} {required && <span className="req">*</span>}</label>
      {children}
      {errors[field] && <span className="err-text">{errors[field]}</span>}
    </div>
  );
}

function ProfileForm({mode,initialType,initialData,onCancel,onSave}){
  const [type,setType]=useState(initialType||"professional");
  const blankPro = {name:"",mobile:"",email:"",district:"",experience:"",company:"",designation:"",ctc:"",education:"",marital:"",address:""};
  const blankStu = {name:"",mobile:"",email:"",district:"",specialization:"",gradYear:"",education:"",marital:"",address:""};
  const [data,setData]=useState(initialData || (type==="professional"?blankPro:blankStu));
  const [errors,setErrors]=useState({});

  useEffect(()=>{
    if(!initialData){ setData(type==="professional"?blankPro:blankStu); setErrors({}); }
  },[type]);

  function update(field,val){ setData(d=>({...d,[field]:val})); }

  function handleFileSelect(e){
    const file = e.target.files && e.target.files[0];
    if(!file) return;
    if(file.size > 5 * 1024 * 1024){
      alert("Please select an image smaller than 5MB.");
      return;
    }
    const reader = new FileReader();
    reader.onload = (evt)=>{
      update("photo", evt.target.result);
    };
    reader.readAsDataURL(file);
  }

  function validate(){
    const e={};
    if(!data.name || !data.name.trim()) e.name="Full name is required.";
    if(!data.mobile || !/^\d{10}$/.test(data.mobile)) e.mobile="Enter a valid 10-digit mobile number.";
    if(!data.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) e.email="Enter a valid email address.";
    if(!data.district) e.district="Current district is required.";
    if(!data.education) e.education="Education qualification is required.";
    if(type==="professional"){
      if(!data.experience) e.experience="Experience is required.";
      if(!data.company || !data.company.trim()) e.company="Current company is required.";
      if(!data.designation || !data.designation.trim()) e.designation="Current designation is required.";
    } else {
      if(!data.specialization) e.specialization="Specialization is required.";
      if(!data.gradYear) e.gradYear="Year of graduation is required.";
    }
    setErrors(e);
    return Object.keys(e).length===0;
  }

  function submit(){
    if(!validate()) return;
    onSave(type,data);
  }


  return (
    <div>
      <div className="page-head">
        <div><h1 className="font-display">{mode==="add" ? "Add Profile" : (type==="professional" ? "Edit Professional Profile" : "Edit Student Profile")}</h1>
        <p>{mode==="add" ? "Choose a profile type and fill in the details below." : "Update the profile details below."}</p></div>
      </div>

      <div className="section-card">
        {mode==="add" && (
          <div className="tab-toggle">
            <button className={type==="professional"?"active":""} onClick={()=>setType("professional")}>Working Professional</button>
            <button className={type==="student"?"active":""} onClick={()=>setType("student")}>Student</button>
          </div>
        )}

        <div className="form-grid">
          <Field errors={errors} field="name" label="Full Name" required><input value={data.name} onChange={e=>update("name",e.target.value)} placeholder="e.g. Karthik R"/></Field>
          <Field errors={errors} field="mobile" label="Mobile Number" required><input value={data.mobile} onChange={e=>update("mobile",e.target.value.replace(/\D/g,"").slice(0,10))} placeholder="10-digit number"/></Field>
          <Field errors={errors} field="email" label="Email ID" required><input value={data.email} onChange={e=>update("email",e.target.value)} placeholder="name@example.com"/></Field>
          <Field errors={errors} field="district" label="Current District" required>
            <select value={data.district} onChange={e=>update("district",e.target.value)}>
              <option value="">Select district</option>
              {DISTRICTS.map(d=><option key={d} value={d}>{d}</option>)}
            </select>
          </Field>

          <Field errors={errors} field="photo" label="Photo Upload (File or Image/Google Drive Link)" span>
            <div className="photo-upload" style={{display:'flex',gap:16,alignItems:'center'}}>
              <Avatar name={data.name||"New User"} size={60} photo={data.photo}/>
              <div style={{display:'flex',flexDirection:'column',gap:8,flex:1}}>
                <div style={{display:'flex',gap:10,alignItems:'center'}}>
                  <label className="btn btn-secondary btn-sm" style={{cursor:'pointer',margin:0}}>
                    <I.Plus style={{width:14,height:14}}/> Choose Image File
                    <input type="file" accept="image/*" style={{display:'none'}} onChange={handleFileSelect}/>
                  </label>
                  {data.photo && (
                    <button type="button" className="btn btn-ghost btn-sm" style={{color:'var(--red)'}} onClick={()=>update("photo","")}>
                      <I.Trash style={{width:14,height:14}}/> Remove Photo
                    </button>
                  )}
                </div>
                <input type="text" value={data.photo||""} onChange={e=>update("photo",e.target.value)} placeholder="Or paste image URL / Google Drive photo link..." style={{fontSize:12.5,padding:'8px 12px',borderRadius:8,border:'1px solid var(--border)'}}/>
              </div>
            </div>
          </Field>

          {type==="professional" ? (
            <>
              <Field errors={errors} field="experience" label="Total Years of Experience" required><input type="number" step="0.1" value={data.experience} onChange={e=>update("experience",e.target.value)} placeholder="e.g. 4.5"/></Field>
              <Field errors={errors} field="company" label="Current Company" required><input value={data.company} onChange={e=>update("company",e.target.value)} placeholder="e.g. Cognizant"/></Field>
              <Field errors={errors} field="designation" label="Current Designation" required><input value={data.designation} onChange={e=>update("designation",e.target.value)} placeholder="e.g. Software Engineer"/></Field>
              <Field errors={errors} field="ctc" label="Current CTC"><input value={data.ctc} onChange={e=>update("ctc",e.target.value)} placeholder="e.g. 8.5 LPA"/></Field>
            </>
          ):(
            <>
              <Field errors={errors} field="specialization" label="Specialization" required>
                <select value={data.specialization} onChange={e=>update("specialization",e.target.value)}>
                  <option value="">Select specialization</option>
                  {SPECIALIZATIONS.map(s=><option key={s} value={s}>{s}</option>)}
                </select>
              </Field>
              <Field errors={errors} field="gradYear" label="Year of Graduation" required>
                <select value={data.gradYear} onChange={e=>update("gradYear",e.target.value)}>
                  <option value="">Select year</option>
                  {GRAD_YEARS.map(y=><option key={y} value={y}>{y}</option>)}
                </select>
              </Field>
            </>
          )}

          <Field errors={errors} field="education" label="Education Qualification" required>
            <select value={data.education} onChange={e=>update("education",e.target.value)}>
              <option value="">Select qualification</option>
              {/* Keep a saved value like "B.E (CSE)" selectable even though it isn't a preset. */}
              {(!data.education || EDUCATIONS.includes(data.education) ? EDUCATIONS : [...EDUCATIONS, data.education]).map(e=><option key={e} value={e}>{e}</option>)}
            </select>
          </Field>
          <Field errors={errors} field="marital" label="Marital Status">
            <select value={data.marital||""} onChange={e=>update("marital",e.target.value)}>
              <option value="">Select status</option>
              {MARITAL_STATUSES.map(m=><option key={m} value={m}>{m}</option>)}
            </select>
          </Field>
          <Field errors={errors} field="address" label="Current Address" span><textarea rows="3" value={data.address} onChange={e=>update("address",e.target.value)} placeholder="Door No, Street, Area, District, State"/></Field>
        </div>

        <div className="form-actions">
          <button className="btn btn-secondary" onClick={onCancel}>Cancel</button>
          <button className="btn btn-primary" onClick={submit}>{mode==="add" ? "Save Profile" : "Save Changes"}</button>
        </div>
      </div>
    </div>
  );
}

/* ============ DELETE MODAL ============ */
function DeleteModal({name,onCancel,onConfirm}){
  return (
    <div className="modal-overlay" onClick={onCancel}>
      <div className="modal-card" onClick={e=>e.stopPropagation()}>
        <div className="modal-icon"><I.Alert/></div>
        <h3>Delete Profile?</h3>
        <p>Are you sure you want to delete <strong>{name}</strong>'s profile? This action cannot be undone.</p>
        <div className="modal-actions">
          <button className="btn btn-secondary" onClick={onCancel}>Cancel</button>
          <button className="btn btn-danger" onClick={onConfirm}>Delete Profile</button>
        </div>
      </div>
    </div>
  );
}

/* ============ ADMIN LOGIN MODAL ============ */
function LoginModal({onCancel,onSubmit}){
  const [email,setEmail]=useState("");
  const [password,setPassword]=useState("");
  const [error,setError]=useState("");
  const [loading,setLoading]=useState(false);
  const [showPw,setShowPw]=useState(false);

  async function submit(){
    if(loading) return;
    setLoading(true); setError("");
    const result = await onSubmit(email.trim(), password);
    setLoading(false);
    if(!result.success) setError(result.error || "Incorrect email or password.");
  }


  return (
    <div className="modal-overlay" onClick={onCancel}>
      <div className="modal-card" onClick={e=>e.stopPropagation()}>
        <div className="modal-icon" style={{background:'#EEF0FF',color:'var(--indigo)'}}><I.Shield/></div>
        <h3>Admin Sign In</h3>
        <p>Sign in with an admin account to add or delete profiles.</p>
        <div className="login-field">
          <label>Email Address</label>
          <input value={email} onChange={e=>{setEmail(e.target.value);setError("");}}
            placeholder="learningeethal@gmail.com"
            onKeyDown={e=>{ if(e.key==='Enter') submit(); }}
            style={{width:'100%',boxSizing:'border-box'}}/>
        </div>
        <div className="login-field">
          <label>Password</label>
          <div style={{position:'relative',display:'flex',alignItems:'center'}}>
            <input
              type={showPw ? "text" : "password"}
              value={password}
              onChange={e=>{setPassword(e.target.value);setError("");}}
              placeholder="Enter password"
              onKeyDown={e=>{ if(e.key==='Enter') submit(); }}
              style={{width:'100%',paddingRight:42,boxSizing:'border-box'}}/>
            <button
              type="button"
              onClick={()=>setShowPw(s=>!s)}
              style={{position:'absolute',right:10,background:'none',border:'none',color:'var(--text-faint)',cursor:'pointer',padding:4,display:'flex',alignItems:'center'}}
              title={showPw ? "Hide password" : "Show password"}>
              {showPw ? (
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              ) : (
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              )}
            </button>
          </div>
        </div>
        {error && <p style={{color:'var(--red)',fontSize:12.5,margin:'-4px 0 12px',display:'flex',alignItems:'center',gap:5}}>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke="#fff" strokeWidth="2" fill="none" strokeLinecap="round"/></svg>
          {error}
        </p>}
        <div className="modal-actions">
          <button className="btn btn-secondary" onClick={onCancel} disabled={loading}>Cancel</button>
          <button className="btn btn-primary" onClick={submit} disabled={loading}>{loading ? "Signing in..." : "Sign In"}</button>
        </div>
      </div>
    </div>
  );
}

/* ============ APP ============ */
function App(){
  const [role,setRole]=useState(CONFIG.user ? "admin" : "viewer"); // "admin" | "viewer"
  const isAdmin = role==="admin";
  const [adminUser,setAdminUser]=useState(CONFIG.user);
  const [loginOpen,setLoginOpen]=useState(false);
  const [view,setView]=useState(CONFIG.view || "dashboard");
  const [professionals,setProfessionals]=useState([]);
  const [students,setStudents]=useState([]);
  const [dataLoading,setDataLoading]=useState(true);
  const [dataError,setDataError]=useState("");
  const [notifications]=useState(NOTIFICATIONS);

  const [detail,setDetail]=useState(null); // {type, id}
  const [formState,setFormState]=useState(null); // {mode, type, data}
  const [deleteTarget,setDeleteTarget]=useState(null); // {type, item}
  const [toast,setToast]=useState("");

  const [globalSearch,setGlobalSearch]=useState("");
  const [notifOpen,setNotifOpen]=useState(false);
  const [profileOpen,setProfileOpen]=useState(false);

  async function loadData(){
    setDataLoading(true); setDataError("");
    try{
      const [pRes,sRes] = await Promise.all([api("professionals"), api("students")]);
      if(!pRes.ok || !sRes.ok) throw new Error("Server responded with an error.");
      const [pData,sData] = await Promise.all([pRes.json(), sRes.json()]);
      setProfessionals(pData);
      setStudents(sData);
    }catch(err){
      setDataError("Couldn't load profiles from the Eethal Learning site. Please reload this page.");
    }finally{
      setDataLoading(false);
    }
  }
  useEffect(()=>{ loadData(); },[]);

  // Dashboard, Working Professionals and Students each have their own WordPress page URL.
  const changeView = useCallback((v)=>{
    setView(v); setDetail(null); setFormState(null);
    const url = CONFIG.pages[v];
    if(url && new URL(url).pathname !== window.location.pathname) window.history.pushState({view:v}, "", url);
  }, []);
  useEffect(()=>{
    function onPop(){
      const v = viewFromPath(window.location.pathname);
      if(v){ setView(v); setDetail(null); setFormState(null); }
    }
    window.addEventListener("popstate", onPop);
    return ()=>window.removeEventListener("popstate", onPop);
  },[]);
  // The header search shows results as you type; clearing it returns to the page you were on.
  const searchReturnView = useRef("dashboard");
  const handleSearchSubmit = useCallback(()=>{
    if(!globalSearch.trim()) return;
    if(view!=="search") searchReturnView.current = view;
    changeView("search");
  }, [globalSearch, view, changeView]);
  const handleSearchChange = useCallback((value)=>{
    setGlobalSearch(value);
    if(value.trim()){
      // Typing in the header shouldn't throw away an open add/edit form; Enter still searches.
      if(view!=="search"){ if(!formState){ searchReturnView.current = view; changeView("search"); } }
      else if(detail){ setDetail(null); }
    } else if(view==="search"){
      changeView(searchReturnView.current);
    }
  }, [view, formState, detail, changeView]);
  const openDetail = useCallback((type,id)=>{ setDetail({type,id}); setFormState(null); }, []);
  const openAdd = useCallback(()=>{ setFormState({mode:"add", type: view==="students"?"student":"professional", data:null}); setDetail(null); }, [view]);
  const openEdit = useCallback((type,item)=>{ setFormState({mode:"edit", type, data:item}); setDetail(null); }, []);
  const openDelete = useCallback((type,item)=>{ setDeleteTarget({type,item}); }, []);

  async function handleLoginSubmit(email,password){
    try{
      const res = await api("auth/login", {
        method:"POST",
        body: JSON.stringify({email,password}),
      });
      const data = await res.json().catch(()=>({}));
      if(!res.ok) return {success:false, error: data.message || "Incorrect email or password."};
      restNonce = data.nonce;
      setRole("admin"); setAdminUser({name:data.name, email:data.email}); setLoginOpen(false);
      setToast("Signed in as "+data.name.split(" ")[0]+".");
      return {success:true};
    }catch(err){
      return {success:false, error:"Couldn't reach the server. Please try again."};
    }
  }
  async function handleLogout(){
    try{
      const res = await api("auth/logout", {method:"POST"});
      const data = await res.json().catch(()=>({}));
      if(data.nonce) restNonce = data.nonce;
    }catch(err){}
    setRole("viewer"); setAdminUser(null); setProfileOpen(false);
    changeView("dashboard");
    setToast("Signed out.");
  }

  async function handleSave(type,data){
    const endpoint = type==="professional" ? "professionals" : "students";
    try{
      if(formState.mode==="add"){
        const res = await api(endpoint, {
          method:"POST", body: JSON.stringify(data),
        });
        if(!res.ok) throw new Error("create failed");
        const record = await res.json();
        if(type==="professional") setProfessionals(list=>[record,...list]);
        else setStudents(list=>[record,...list]);
        setToast("Profile added successfully.");
      } else {
        const res = await api(endpoint+"/"+data.id, {
          method:"PUT", body: JSON.stringify(data),
        });
        if(!res.ok) throw new Error("update failed");
        const record = await res.json();
        if(type==="professional") setProfessionals(list=>list.map(p=>p.id===record.id?record:p));
        else setStudents(list=>list.map(s=>s.id===record.id?record:s));
        setToast("Profile updated successfully.");
        if(detail && detail.id===record.id) setDetail({type,id:record.id});
      }
      setFormState(null);
    }catch(err){
      setToast("Couldn't save changes. Check the server connection.");
    }
  }

  async function confirmDelete(){
    const {type,item}=deleteTarget;
    const endpoint = (type==="professional" ? "professionals/" : "students/") + item.id;
    try{
      const res = await api(endpoint, {method:"DELETE"});
      if(!res.ok) throw new Error("delete failed");
      if(type==="professional") setProfessionals(list=>list.filter(p=>p.id!==item.id));
      else setStudents(list=>list.filter(s=>s.id!==item.id));
      setDeleteTarget(null);
      if(detail && detail.id===item.id) setDetail(null);
      setToast("Profile deleted successfully.");
    }catch(err){
      setDeleteTarget(null);
      setToast("Couldn't delete. Check the server connection.");
    }
  }

  const detailData = detail ? (detail.type==="professional" ? professionals.find(p=>p.id===detail.id) : students.find(s=>s.id===detail.id)) : null;

  let mainContent;
  if(dataLoading){
    mainContent = (
      <div className="empty-state">
        <I.Empty style={{color:'#C7CADC'}}/>
        <h3>Loading Eethal Learning&hellip;</h3>
        <p>Fetching profiles from the server.</p>
      </div>
    );
  } else if(dataError){
    mainContent = (
      <div className="empty-state">
        <I.Alert style={{color:'var(--red)',width:56,height:56}}/>
        <h3>Can&rsquo;t reach the server</h3>
        <p>{dataError}</p>
        <button className="btn btn-primary" onClick={loadData}>Try Again</button>
      </div>
    );
  } else if(formState){
    mainContent = <ProfileForm mode={formState.mode} initialType={formState.type} initialData={formState.data} onCancel={()=>setFormState(null)} onSave={handleSave}/>;
  } else if(detail && detailData){
    mainContent = <ProfileDetailPage type={detail.type} data={detailData}
      onBack={()=>setDetail(null)}
      onEdit={()=>openEdit(detail.type,detailData)}
      onDelete={()=>openDelete(detail.type,detailData)}
      isAdmin={isAdmin}/>;
  } else if(view==="dashboard"){
    mainContent = <Dashboard professionals={professionals} students={students} setView={changeView} openDetail={openDetail}/>;
  } else if(view==="professionals"){
    mainContent = <ProfessionalsPage professionals={professionals} openDetail={openDetail} openAdd={openAdd} openEdit={(t,i)=>openEdit(t,i)} openDelete={(t,i)=>openDelete(t,i)} isAdmin={isAdmin}/>;
  } else if(view==="students"){
    mainContent = <StudentsPage students={students} openDetail={openDetail} openAdd={openAdd} openEdit={(t,i)=>openEdit(t,i)} openDelete={(t,i)=>openDelete(t,i)} isAdmin={isAdmin}/>;
  } else if(view==="search"){
    mainContent = <SearchResultsPage query={globalSearch} professionals={professionals} students={students} openDetail={openDetail} openEdit={(t,i)=>openEdit(t,i)} openDelete={(t,i)=>openDelete(t,i)} isAdmin={isAdmin}/>;
  } else {
    mainContent = (
      <div>
        <div className="page-head"><div><h1 className="font-display">{view.charAt(0).toUpperCase()+view.slice(1)}</h1><p>This section is under construction.</p></div></div>
        <EmptyState title="Coming Soon" subtitle="This part of Eethal Learning is being built out. Check back shortly."/>
      </div>
    );
  }

  return (
    <div className={"app-shell"+(isAdmin?" with-sidebar":"")}>
      {isAdmin && <Sidebar view={view} setView={changeView} onLogout={handleLogout}/>}
      <div className="main-area">
        <TopHeader view={view} setView={changeView} globalSearch={globalSearch} setGlobalSearch={handleSearchChange} onSearchSubmit={handleSearchSubmit}
          notifOpen={notifOpen} setNotifOpen={setNotifOpen} profileOpen={profileOpen} setProfileOpen={setProfileOpen}
          notifications={notifications} isAdmin={isAdmin} adminUser={adminUser}
          onRequestLogin={()=>setLoginOpen(true)} onLogout={handleLogout}/>
        <main className="content">{mainContent}</main>
      </div>
      <MobileBottomNav view={view} setView={changeView}/>
      {deleteTarget && <DeleteModal name={deleteTarget.item.name} onCancel={()=>setDeleteTarget(null)} onConfirm={confirmDelete}/>}
      {loginOpen && <LoginModal onCancel={()=>setLoginOpen(false)} onSubmit={handleLoginSubmit}/>}
      <Toast message={toast} onClose={()=>setToast("")}/>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App/>);
