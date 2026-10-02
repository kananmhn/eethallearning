// Talent Directory app: Dashboard, Working Professionals, Students, New Entries, Enrollments
// and the public Entry Form. Shared pieces live in core.js, icons.jsx and shared.jsx;
// the Enroll Now form and the Enrollments list live in enroll/.
import { CONFIG, api, setRestNonce } from "./core.js";
import { I } from "./icons.jsx";
import {
  BrandLogo, GRADIENTS, hashStr, tidy, tidyEducation, sameText, uniqueOptions,
  Avatar, EmptyState, FilterSelect, DetailField, Field, formatDate,
} from "./shared.jsx";
import { EnrollFormApp } from "./enroll/EnrollFormApp.jsx";
import { EnrollmentsPage } from "./enroll/EnrollmentsPage.jsx";

const { useState, useEffect, useMemo, useRef, useCallback } = React;

function viewFromPath(pathname){
  const clean = p => p.replace(/\/+$/,"");
  const match = Object.keys(CONFIG.pages).find(k => clean(new URL(CONFIG.pages[k]).pathname) === clean(pathname));
  return match || null;
}



// Profile type names, shown as a label on forms, entries and profiles.
const TYPE_LABEL = {professional:"Working Professional", student:"Student"};
function TypeChip({type}){
  const Icon = type==="student" ? I.Grad : I.Users;
  return <span className={"type-chip type-chip--"+type}><Icon/> {TYPE_LABEL[type]}</span>;
}

/* ============ MOCK DATA ============ */
const DISTRICTS = ["Chennai","Coimbatore","Madurai","Trichy","Salem","Erode","Vellore","Tirunelveli"];
const DESIGNATIONS = ["Software Engineer","Senior Software Engineer","Product Manager","Data Analyst","UX Designer","Business Analyst","DevOps Engineer","HR Manager"];
const COMPANIES = ["Cognizant","TCS","Infosys","Wipro","Zoho","Freshworks","HCLTech","Accenture"];
const EDUCATIONS = ["B.E","B.Tech","M.E","M.Tech","MBA","MCA","B.Sc","M.Sc"];
const SPECIALIZATIONS = ["Computer Science Engineering","Information Technology","Electronics & Communication","Mechanical Engineering","Data Science","AI & Machine Learning","Business Administration","Commerce"];
const GRAD_YEARS = [2023,2024,2025,2026,2027];
const MARITAL_STATUSES = ["Married","Unmarried"];
const ADMIN_GRADIENT = ["#5B5FEF","#8B5CF6"];

function pick(arr,seed){ return arr[seed % arr.length]; }

// Marital filter: "NA" picks profiles with no marital status, matching the "NA" shown on cards.
const MARITAL_FILTER_OPTS = [...MARITAL_STATUSES,"NA"];
const matchesMarital = (value,filter) => !filter || (filter==="NA" ? !tidy(value) : sameText(value,filter));

// Notifications come from the server's activity log (GET notifications). "Unread" means
// newer than the last time this browser opened the bell, kept in localStorage.
const NOTIF_SEEN_KEY = "eethal_td_notif_seen";
function readNotifSeen(){ try{ return localStorage.getItem(NOTIF_SEEN_KEY) || ""; }catch(e){ return ""; } }
function writeNotifSeen(iso){ try{ localStorage.setItem(NOTIF_SEEN_KEY, iso); }catch(e){} }

function timeAgo(iso){
  const secs = Math.max(0, (Date.now() - new Date(iso).getTime()) / 1000);
  if(secs < 60) return "Just now";
  const mins = Math.floor(secs/60);
  if(mins < 60) return mins+" min ago";
  const hours = Math.floor(mins/60);
  if(hours < 24) return hours+(hours===1?" hour ago":" hours ago");
  const days = Math.floor(hours/24);
  if(days === 1) return "Yesterday";
  if(days < 7) return days+" days ago";
  return new Date(iso).toLocaleDateString("en-IN",{day:"numeric",month:"short",year:"numeric"});
}

/* ============ SMALL COMPONENTS ============ */

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
function Sidebar({view,setView,onSignOut,pendingCount}){
  const items = [
    {key:"dashboard",label:"Dashboard",icon:I.Home},
    {key:"professionals",label:"Working Professionals",icon:I.Users},
    {key:"students",label:"Students",icon:I.Grad},
    {key:"entries",label:"New Entries",icon:I.Inbox,count:pendingCount},
    {key:"enrollments",label:"Enrollments",icon:I.Form},
  ];
  return (
    <aside className="sidebar">
      <div className="sidebar-logo-row">
        <BrandLogo onDark/>
      </div>
      <div className="sidebar-badge"><I.Shield style={{width:14,height:14}}/> Talent Pool Admin</div>
      <nav className="nav-section">
        {items.map(it=>(
          <button key={it.key} className={"nav-item"+(view===it.key?" active":"")} onClick={()=>setView(it.key)}>
            <it.icon/> {it.label}
            {it.count>0 && <span className="nav-count">{it.count}</span>}
          </button>
        ))}
        <div className="nav-divider"/>
        <button className="nav-item" onClick={onSignOut}><I.Logout/> Sign Out</button>
      </nav>
    </aside>
  );
}

function TopHeader({view, setView, globalSearch, setGlobalSearch, onSearchSubmit, notifOpen, setNotifOpen, profileOpen, setProfileOpen, notifications, onNotifOpen, onNotifClick, isAdmin, adminUser, onSignOut}){
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
    setProfileOpen(o=>!o);
  }
  const firstName = adminUser?.name?.split(" ")[0] || (isAdmin ? "Admin" : "Viewer");

  return (
    <div className="header-shell">
      <header className="topbar">
        {!isAdmin && (
          <a className="brand-row" href={CONFIG.homeUrl}>
            <BrandLogo/>
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
          {isAdmin && <div ref={notifRef} style={{position:'relative'}}>
            <button className="icon-btn" aria-label={unread>0 ? `Notifications (${unread} new)` : "Notifications"}
              onClick={()=>{ if(!notifOpen) onNotifOpen(); setNotifOpen(o=>!o); setProfileOpen(false); }}>
              <I.Bell/>{unread>0 && <span className="badge-count">{unread>9?"9+":unread}</span>}
            </button>
            {notifOpen && (
              <div className="notif-panel">
                <div className="notif-head">Notifications {unread>0 && <span className="notif-new">{unread} new</span>}</div>
                {notifications.length===0 ? (
                  <div className="notif-empty">No notifications yet.</div>
                ) : notifications.map(n=>(
                  <button key={n.id} type="button" className={"notif-item notif-item--"+n.kind+(n.unread?"":" read")+(n.link?" is-link":"")}
                    onClick={()=>{ if(n.link){ setNotifOpen(false); onNotifClick(n); } }}>
                    <div className="notif-dot"/>
                    <div><div className="notif-text">{n.text}</div><div className="notif-time">{timeAgo(n.time)}</div></div>
                  </button>
                ))}
              </div>
            )}
          </div>}
          <div ref={profRef} style={{position:'relative'}}>
            <button className="admin-chip" onClick={handleChipClick}>
              <Avatar name={adminUser?.name || (isAdmin ? "Admin" : "Viewer")} size={30} gradient={isAdmin?ADMIN_GRADIENT:undefined}/>
              <span className="chip-name">
                <span style={{fontSize:13,fontWeight:600}}>{firstName}</span>
                <span className={"chip-role"+(isAdmin?" is-admin":"")}>{isAdmin ? "Admin" : "Viewer"}</span>
              </span>
              <I.Chevron/>
            </button>
            {profileOpen && (
              <div className="dropdown-menu" style={{right:0,top:50,minWidth:210}}>
                {adminUser?.email && <div className="dropdown-user">Signed in as <strong>{adminUser.email}</strong></div>}
                <button className="danger" onClick={()=>{setProfileOpen(false);onSignOut();}}><I.Logout style={{width:14,height:14}}/> Sign Out</button>
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

function MobileBottomNav({view,setView,isAdmin}){
  const items=[{key:"dashboard",label:"Home",icon:I.Home},{key:"professionals",label:"Professionals",icon:I.Users},{key:"students",label:"Students",icon:I.Grad}];
  if(isAdmin) items.push({key:"entries",label:"Entries",icon:I.Inbox},{key:"enrollments",label:"Enrolls",icon:I.Form});
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
function StatCard({icon,color,bg,value,label,onClick}){
  return (
    <button type="button" className="stat-card stat-card--link" onClick={onClick} style={{"--stat-c":color}} aria-label={`${label}: ${value}. View all`}>
      <div className="stat-top">
        <div className="stat-icon" style={{background:bg,color:color}}>{icon}</div>
        <span className="stat-go">View all <I.Arrow/></span>
      </div>
      <div className="stat-value">{value}</div>
      <div className="stat-label">{label}</div>
    </button>
  );
}

function Dashboard({professionals,students,setView,openDetail,isAdmin,pendingCount}){
  // Three of each fills one row of the 3-column card grid.
  const recentPros = [...professionals].sort((a,b)=>new Date(b.createdAt)-new Date(a.createdAt)).slice(0,3);
  const recentStu = [...students].sort((a,b)=>new Date(b.createdAt)-new Date(a.createdAt)).slice(0,3);
  return (
    <div>
      <div className="page-head">
        <div><h1 className="font-display">Dashboard</h1><p>Manage and discover professionals and students.</p></div>
        {isAdmin && (
          <button className="btn btn-primary new-entry-btn" onClick={()=>setView("entries")}>
            <I.Inbox/> New Entry
            {pendingCount>0 && <span className="new-entry-count">{pendingCount}</span>}
          </button>
        )}
      </div>
      {isAdmin && pendingCount>0 && (
        <button className="entry-callout" onClick={()=>setView("entries")}>
          <span className="entry-callout-ic"><I.Inbox/></span>
          <span className="entry-callout-tx">
            <strong>{pendingCount} new {pendingCount===1?"entry is":"entries are"} waiting for review</strong>
            <span>Approve them to add the details to Working Professionals or Students.</span>
          </span>
          <span className="entry-callout-go">Review <I.Arrow/></span>
        </button>
      )}
      <div className="stat-grid">
        <StatCard icon={<I.Users/>} color="#5B5FEF" bg="#EEF0FF" value={professionals.length} label="Working Professionals" onClick={()=>setView("professionals")}/>
        <StatCard icon={<I.Grad/>} color="#0FA981" bg="#E4F7F1" value={students.length} label="Students" onClick={()=>setView("students")}/>
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


function ProfessionalsPage({professionals,openDetail,openAdd,openEdit,openDelete,isAdmin}){
  const [search,setSearch]=useState("");
  const [f,setF]=useState({district:"",experience:"",designation:"",company:"",education:"",marital:""});
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
      const matchesMar = matchesMarital(p.marital,f.marital);
      const exp = parseFloat(p.experience) || 0;
      const matchesExp = !f.experience || (
        f.experience==="0-2" ? exp<2 :
        f.experience==="2-5" ? (exp>=2 && exp<5) :
        f.experience==="5-8" ? (exp>=5 && exp<8) : exp>=8
      );
      return matchesSearch && matchesDistrict && matchesDesig && matchesCompany && matchesEdu && matchesMar && matchesExp;
    });
  },[professionals,search,f]);

  const pageItems = filtered.slice(0,page*pageSize);
  const resetFilters=()=>{setSearch("");setF({district:"",experience:"",designation:"",company:"",education:"",marital:""});setPage(1);};
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
          <FilterSelect value={f.marital} onChange={v=>{setF({...f,marital:v});setPage(1);}} options={MARITAL_FILTER_OPTS} placeholder="Marital Status"/>
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
  const [f,setF]=useState({gradYear:"",specialization:"",district:"",education:"",marital:""});
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
      const matchesMar = matchesMarital(s.marital,f.marital);
      return matchesSearch && matchesYear && matchesSpec && matchesDistrict && matchesEdu && matchesMar;
    });
  },[students,search,f]);

  const pageItems = filtered.slice(0,page*pageSize);
  const resetFilters=()=>{setSearch("");setF({gradYear:"",specialization:"",district:"",education:"",marital:""});setPage(1);};
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
          <FilterSelect value={f.marital} onChange={v=>{setF({...f,marital:v});setPage(1);}} options={MARITAL_FILTER_OPTS} placeholder="Marital Status"/>
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
  professional: ["name","company","designation","district","education","ctc","experience","email","mobile","batch"],
  student: ["name","specialization","education","district","gradYear","email","mobile","batch"],
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
            <TypeChip type={type}/>
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
            <DetailField label="Batch No" value={data.batch}/>
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

// The profile type picker: a labelled choice when adding, a fixed label when editing.
const TYPE_OPTIONS = [
  {key:"professional", icon:I.Users, hint:"Currently working at a company"},
  {key:"student", icon:I.Grad, hint:"Studying or recently graduated"},
];
function TypePicker({type,setType,locked}){
  return (
    <div className="type-picker">
      <div className="type-picker-label">Profile Type {!locked && <span className="req">*</span>}</div>
      <div className="type-picker-options" role="radiogroup" aria-label="Profile Type">
        {TYPE_OPTIONS.filter(o=>!locked || o.key===type).map(o=>(
          <button key={o.key} type="button" role="radio" aria-checked={type===o.key} disabled={locked}
            className={"type-option type-option--"+o.key+(type===o.key?" active":"")} onClick={()=>setType(o.key)}>
            <span className="type-option-ic"><o.icon/></span>
            <span className="type-option-tx">
              <span className="type-option-title">{TYPE_LABEL[o.key]}</span>
              <span className="type-option-hint">{o.hint}</span>
            </span>
            {!locked && <span className="type-option-check"><I.Check/></span>}
          </button>
        ))}
      </div>
    </div>
  );
}

// mode: "add" and "edit" are the admin's forms; "entry" is the public Entry Form, where
// onSave returns a promise that may reject with {fields} errors from the server.
function ProfileForm({mode,initialType,initialData,onCancel,onSave}){
  const [type,setType]=useState(initialType||"professional");
  const blankPro = {name:"",mobile:"",email:"",batch:"",district:"",experience:"",company:"",designation:"",ctc:"",education:"",marital:"",address:""};
  const blankStu = {name:"",mobile:"",email:"",batch:"",district:"",specialization:"",gradYear:"",education:"",marital:"",address:""};
  const [data,setData]=useState(initialData || (type==="professional"?blankPro:blankStu));
  const [errors,setErrors]=useState({});
  const [busy,setBusy]=useState(false);
  const [formError,setFormError]=useState("");
  const isEntry = mode==="entry";

  useEffect(()=>{
    // Keep the common fields when switching type, so nobody has to retype their name.
    if(!initialData){
      const blank = type==="professional"?blankPro:blankStu;
      setData(d=>({...Object.fromEntries(Object.keys(blank).map(k=>[k, k in d ? d[k] : blank[k]])), photo:d.photo||""}));
      setErrors({});
    }
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

  async function submit(){
    if(busy) return;
    setFormError("");
    if(!validate()){ setFormError("Please correct the highlighted fields."); return; }
    setBusy(true);
    try{
      await onSave(type,data);
    }catch(err){
      if(err && err.fields) setErrors(err.fields);
      setFormError((err && err.message) || "Couldn't submit the form. Please try again.");
    }finally{
      setBusy(false);
    }
  }

  return (
    <div>
      {!isEntry && (
        <div className="page-head">
          <div><h1 className="font-display">{mode==="add" ? "Add Profile" : (type==="professional" ? "Edit Professional Profile" : "Edit Student Profile")}</h1>
          <p>{mode==="add" ? "Choose a profile type and fill in the details below." : "Update the profile details below."}</p></div>
        </div>
      )}

      <div className="section-card">
        <TypePicker type={type} setType={setType} locked={mode==="edit"}/>

        <div className="form-section-title">{TYPE_LABEL[type]} Details</div>
        <div className="form-grid">
          <Field errors={errors} field="name" label="Full Name" required><input value={data.name} onChange={e=>update("name",e.target.value)} placeholder="e.g. Karthik R"/></Field>
          <Field errors={errors} field="mobile" label="Mobile Number" required><input value={data.mobile} onChange={e=>update("mobile",e.target.value.replace(/\D/g,"").slice(0,10))} placeholder="10-digit number"/></Field>
          <Field errors={errors} field="email" label="Email ID" required><input value={data.email} onChange={e=>update("email",e.target.value)} placeholder="name@example.com"/></Field>
          <Field errors={errors} field="batch" label="Batch No"><input value={data.batch||""} onChange={e=>update("batch",e.target.value)} placeholder="e.g. Batch 12"/></Field>
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
          {/* Left empty by people; bots fill it in. */}
          {isEntry && <input className="hp-field" tabIndex="-1" autoComplete="off" aria-hidden="true" value={data.website||""} onChange={e=>update("website",e.target.value)}/>}
        </div>

        {formError && <div className="form-error-banner"><I.Alert/> {formError}</div>}
        <div className="form-actions">
          {onCancel && <button className="btn btn-secondary" onClick={onCancel} disabled={busy}>Cancel</button>}
          <button className="btn btn-primary" onClick={submit} disabled={busy}>
            {isEntry ? <><I.Send/> {busy ? "Submitting..." : "Submit"}</> : (busy ? "Saving..." : mode==="add" ? "Save Profile" : "Save Changes")}
          </button>
        </div>
      </div>
    </div>
  );
}

/* ============ PUBLIC ENTRY FORM (/entry-form/) ============ */
// Standalone page: people submit their own details, which wait for an admin's approval.
function EntryFormApp(){
  const [submitted,setSubmitted]=useState(null); // {name, type} once sent
  const [formKey,setFormKey]=useState(0);

  async function handleSubmit(type,data){
    const res = await api("entries", {method:"POST", body: JSON.stringify({...data, type})});
    const body = await res.json().catch(()=>({}));
    if(!res.ok) throw {message: body.message || "Couldn't submit the form. Please try again.", fields: body.data && body.data.fields};
    setSubmitted({name:data.name, type});
    window.scrollTo({top:0, behavior:"smooth"});
  }

  return (
    <div className="entry-page">
      <header className="entry-topbar">
        <a className="brand-row" href={CONFIG.homeUrl}>
          <BrandLogo/>
        </a>
      </header>

      <section className="entry-hero">
        <span className="entry-hero-pill"><I.Shield style={{width:14,height:14}}/> Talent Directory</span>
        <h1 className="font-display">Join the Eethal Learning Directory</h1>
        <p>Please fill in your details below so we can keep your Eethal Learning profile up to date.</p>
        {/* <ol className="entry-steps">
          <li><span>1</span> Fill in your details</li>
          <li><span>2</span> Admin reviews them</li>
          <li><span>3</span> Your profile goes live</l
        </ol> */}
      </section>

      <main className="entry-main">
        {submitted ? (
          <div className="section-card entry-success">
            <div className="entry-success-ic"><I.Check/></div>
            <h2 className="font-display">Thank you, {submitted.name.split(" ")[0]}!</h2>
            <p>Your <>{TYPE_LABEL[submitted.type]}</> details have been submitted successfully.</p>
            <button className="btn btn-secondary" onClick={()=>{setSubmitted(null);setFormKey(k=>k+1);}}><I.Plus/> Submit Another Entry</button>
          </div>
        ) : (
          <ProfileForm key={formKey} mode="entry" initialType="professional" onSave={handleSubmit}/>
        )}
      </main>
    </div>
  );
}

/* ============ NEW ENTRIES (admin review) ============ */
const ENTRY_TABS = [
  {key:"pending", label:"Pending"},
  {key:"approved", label:"Approved"},
  {key:"rejected", label:"Rejected"},
  {key:"all", label:"All"},
];


function StatusChip({status}){
  return <span className={"status-chip status-chip--"+status}>{status.charAt(0).toUpperCase()+status.slice(1)}</span>;
}

function ConfirmModal({tone,title,message,confirmLabel,busy,onCancel,onConfirm}){
  return (
    <div className="modal-overlay" onClick={busy?undefined:onCancel}>
      <div className="modal-card" onClick={e=>e.stopPropagation()}>
        <div className={"modal-icon"+(tone==="approve"?" modal-icon--approve":"")}>{tone==="approve" ? <I.Check/> : <I.Alert/>}</div>
        <h3>{title}</h3>
        <p>{message}</p>
        <div className="modal-actions">
          <button className="btn btn-secondary" onClick={onCancel} disabled={busy}>Cancel</button>
          <button className={"btn "+(tone==="approve"?"btn-success":"btn-danger")} onClick={onConfirm} disabled={busy}>{busy ? "Please wait..." : confirmLabel}</button>
        </div>
      </div>
    </div>
  );
}

function EntryDetail({entry,onBack,onReview,onDelete,openProfile}){
  const isP = entry.type==="professional";
  const pending = entry.status==="pending";
  return (
    <div>
      <div className="detail-top">
        <button className="back-btn" onClick={onBack}><I.Back/> Back to Entries</button>
        {pending ? (
          <div style={{display:'flex',gap:8}}>
            <button className="btn btn-reject btn-sm" onClick={()=>onReview(entry,"reject")}><I.Close/> Reject</button>
            <button className="btn btn-success btn-sm" onClick={()=>onReview(entry,"approve")}><I.Check/> Approve</button>
          </div>
        ) : (
          <button className="btn btn-secondary btn-sm" style={{color:'var(--red)'}} onClick={()=>onDelete(entry)}><I.Trash/> Delete Entry</button>
        )}
      </div>

      {entry.duplicate && pending && (
        <div className="entry-warning">
          <I.Alert/>
          <div>A {TYPE_LABEL[entry.duplicate.type]} profile with this email already exists: <button className="link-btn" onClick={()=>openProfile(entry.duplicate.type,entry.duplicate.id)}>{entry.duplicate.name}</button>. Approving will add a second profile.</div>
        </div>
      )}
      {!pending && (
        <div className={"entry-review-note entry-review-note--"+entry.status}>
          {entry.status==="approved" ? <I.CheckCircle/> : <I.Close/>}
          <div>
            {entry.status==="approved" ? "Approved" : "Rejected"}{entry.reviewedBy && <> by <strong>{entry.reviewedBy}</strong></>}{entry.reviewedAt && <> on {formatDate(entry.reviewedAt)}</>}.
            {entry.status==="approved" && entry.profileId>0 && <> <button className="link-btn" onClick={()=>openProfile(entry.type,entry.profileId)}>View profile</button></>}
          </div>
        </div>
      )}

      <div className="profile-header-card">
        <Avatar name={entry.name} size={110} photo={entry.photo}/>
        <div>
          <div className="ph-name-row">
            <h2 className="font-display">{entry.name}</h2>
            <TypeChip type={entry.type}/>
            <StatusChip status={entry.status}/>
          </div>
          <div className="ph-role">{isP ? [entry.designation,entry.company].filter(Boolean).join(" | ") : [entry.specialization,entry.education].filter(Boolean).join(" · ")}</div>
          <div className="ph-contacts">
            <div className="ph-contact-item"><I.Pin/> {entry.district}, Tamil Nadu</div>
            <div className="ph-contact-item"><I.Mail/> {entry.email}</div>
            <div className="ph-contact-item"><I.Phone/> +91 {entry.mobile}</div>
            <div className="ph-contact-item"><I.Clock/> Submitted {formatDate(entry.createdAt)}</div>
          </div>
        </div>
      </div>

      <div className="section-card">
        <div className="section-head"><h3>{TYPE_LABEL[entry.type]} Details</h3></div>
        <div className="detail-grid">
          <div>
            <DetailField label="Full Name" value={entry.name}/>
            <DetailField label="Mobile Number" value={"+91 "+entry.mobile}/>
            <DetailField label="Email ID" value={entry.email}/>
            <DetailField label="Batch No" value={entry.batch}/>
            <DetailField label="Current District" value={entry.district}/>
            {isP
              ? <DetailField label="Total Years of Experience" value={entry.experience && entry.experience+" years"}/>
              : <DetailField label="Specialization" value={entry.specialization}/>}
            <DetailField label="Marital Status" value={entry.marital || "NA"}/>
          </div>
          <div>
            {isP ? (
              <>
                <DetailField label="Current Company" value={entry.company}/>
                <DetailField label="Current Designation" value={entry.designation}/>
                <DetailField label="Current CTC" value={entry.ctc}/>
              </>
            ) : (
              <DetailField label="Year of Graduation" value={entry.gradYear}/>
            )}
            <DetailField label="Education Qualification" value={entry.education}/>
            <DetailField label="Current Address" value={entry.address}/>
          </div>
        </div>
      </div>
    </div>
  );
}

function EntriesPage({entries,loading,onReload,onReview,onDelete,openProfile}){
  // ?view=entries&tab=approved (or rejected, all) opens that tab directly.
  const [tab,setTab]=useState(()=>{
    const t = new URLSearchParams(window.location.search).get("tab");
    return ENTRY_TABS.some(x=>x.key===t) ? t : "pending";
  });
  const [selectedId,setSelectedId]=useState(null);
  // The server leaves out deleted entries; this filter only covers ones deleted since the list loaded.
  const live = useMemo(()=>entries.filter(e=>!e.deleted),[entries]);
  const counts = useMemo(()=>{
    const c = {pending:0,approved:0,rejected:0,all:live.length};
    live.forEach(e=>{ c[e.status]=(c[e.status]||0)+1; });
    return c;
  },[live]);
  const shown = tab==="all" ? live : live.filter(e=>e.status===tab);
  const selected = live.find(e=>e.id===selectedId);

  if(selected){
    return <EntryDetail entry={selected} onBack={()=>setSelectedId(null)} onReview={onReview}
      onDelete={e=>onDelete(e,()=>setSelectedId(null))} openProfile={openProfile}/>;
  }

  return (
    <div>
      <div className="page-head">
        <div><h1 className="font-display">New Entries</h1><p>Review details submitted through the Entry Form. Approved entries are added to the directory.</p></div>
        <div style={{display:'flex',gap:8,flexWrap:'wrap'}}>
          <button className="btn btn-secondary" onClick={onReload} disabled={loading}>{loading ? "Refreshing..." : "Refresh"}</button>
          {CONFIG.pages.entry && <a className="btn btn-primary" href={CONFIG.pages.entry} target="_blank" rel="noopener">Open Entry Form <I.Arrow/></a>}
        </div>
      </div>

      <div className="entry-tabs" role="tablist">
        {ENTRY_TABS.map(t=>(
          <button key={t.key} role="tab" aria-selected={tab===t.key} className={"entry-tab"+(tab===t.key?" active":"")} onClick={()=>setTab(t.key)}>
            {t.label} <span className={"entry-tab-count entry-tab-count--"+t.key}>{counts[t.key]||0}</span>
          </button>
        ))}
      </div>

      {shown.length===0 ? (
        <EmptyState title={tab==="pending" ? "No entries waiting for review" : "Nothing here yet"}
          subtitle={tab==="pending" ? "New submissions from the Entry Form will show up here." : "Entries will appear here once they're reviewed."}/>
      ) : (
        <div className="entry-list">
          {shown.map(e=>(
            <div key={e.id} role="button" tabIndex="0" className={"entry-row entry-row--"+e.status} onClick={()=>setSelectedId(e.id)}
              onKeyDown={ev=>{ if(ev.target===ev.currentTarget && (ev.key==="Enter"||ev.key===" ")){ ev.preventDefault(); setSelectedId(e.id); } }}>
              <Avatar name={e.name} size={46} photo={e.photo}/>
              <div className="entry-row-main">
                <div className="entry-row-name">{e.name} {e.duplicate && e.status==="pending" && <span className="entry-dup" title="A profile with this email already exists">Possible duplicate</span>}</div>
                <div className="entry-row-meta">
                  <span><I.Mail/> {e.email}</span>
                  <span><I.Pin/> {e.district}</span>
                  {e.batch && <span><I.Hash/> Batch {e.batch}</span>}
                </div>
              </div>
              <div className="entry-row-side">
                <TypeChip type={e.type}/>
                <span className="entry-row-date"><I.Clock/> {formatDate(e.createdAt)}</span>
              </div>
              <div className="entry-row-status">
                {e.status==="pending"
                  ? <div className="entry-row-actions" onClick={ev=>ev.stopPropagation()}>
                      <button className="btn btn-reject btn-sm" title="Reject" aria-label={"Reject "+e.name} onClick={()=>onReview(e,"reject")}><I.Close/></button>
                      <button className="btn btn-success btn-sm" onClick={()=>onReview(e,"approve")}><I.Check/> Approve</button>
                    </div>
                  : <StatusChip status={e.status}/>}
              </div>
            </div>
          ))}
        </div>
      )}
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
// The sign-in gate in front of the whole Talent Pool. It can't be dismissed; Cancel is "Back to Home".
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
    <div className="modal-overlay is-locked">
      <div className="modal-card" onClick={e=>e.stopPropagation()}>
        <div className="modal-icon" style={{background:'#EEF0FF',color:'var(--indigo)'}}><I.Shield/></div>
        <h3>Sign in to Talent Pool</h3>
        <p>Please sign in to view working professionals and students.</p>
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
          <button className="btn btn-secondary" onClick={onCancel} disabled={loading}>Back to Home</button>
          <button className="btn btn-primary" onClick={submit} disabled={loading}>{loading ? "Signing in..." : "Sign In"}</button>
        </div>
      </div>
    </div>
  );
}

/* ============ APP ============ */
// The admin emails link to the dashboard with ?view=entries or ?view=enrollments.
const LINKED_VIEW = ["entries","enrollments"].find(v=>v===new URLSearchParams(window.location.search).get("view"));

function App(){
  // "guest": not signed in (sign-in screen). "viewer": Talent Pool Viewer role, read only.
  // "admin": Talent Directory Admin / Administrator role (add, edit, delete, review entries).
  const [role,setRole]=useState(CONFIG.admin ? "admin" : CONFIG.user ? "viewer" : "guest");
  const isAdmin = role==="admin";
  const signedIn = role!=="guest";
  const [adminUser,setAdminUser]=useState(CONFIG.user);
  const [view,setView]=useState(LINKED_VIEW && CONFIG.admin ? LINKED_VIEW : (CONFIG.view || "dashboard"));
  const [enrollments,setEnrollments]=useState([]);
  const [enrollLoading,setEnrollLoading]=useState(false);
  const [deleteEnrollTarget,setDeleteEnrollTarget]=useState(null); // {app, after}
  const [entries,setEntries]=useState([]);
  const [entriesLoading,setEntriesLoading]=useState(false);
  const [reviewTarget,setReviewTarget]=useState(null); // {entry, decision}
  const [deleteEntryTarget,setDeleteEntryTarget]=useState(null); // {entry, after}
  const [reviewBusy,setReviewBusy]=useState(false);
  const pendingCount = entries.filter(e=>e.status==="pending").length;
  const [professionals,setProfessionals]=useState([]);
  const [students,setStudents]=useState([]);
  const [dataLoading,setDataLoading]=useState(true);
  const [dataError,setDataError]=useState("");
  const [notifItems,setNotifItems]=useState([]);
  const [notifSeen,setNotifSeen]=useState(readNotifSeen); // newest time already seen in this browser
  const [panelSeen,setPanelSeen]=useState(""); // keeps dots on the new items while the panel is open

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
  // The Talent Pool is sign-in only: load profiles once signed in, forget them on sign out.
  useEffect(()=>{
    if(signedIn) loadData();
    else { setProfessionals([]); setStudents([]); setDataLoading(false); }
  },[signedIn]);

  async function loadEntries(){
    setEntriesLoading(true);
    try{
      const res = await api("entries");
      if(!res.ok) throw new Error("entries failed");
      setEntries(await res.json());
    }catch(err){
      setToast("Couldn't load new entries. Please try again.");
    }finally{
      setEntriesLoading(false);
    }
  }
  // Admins see the pending count in the sidebar and on the Dashboard.
  useEffect(()=>{ if(isAdmin) loadEntries(); else setEntries([]); },[isAdmin]);

  async function loadEnrollments(){
    setEnrollLoading(true);
    try{
      const res = await api("enrollments");
      if(!res.ok) throw new Error("enrollments failed");
      setEnrollments(await res.json());
    }catch(err){
      setToast("Couldn't load enrollments. Please try again.");
    }finally{
      setEnrollLoading(false);
    }
  }
  // Loaded when an admin first opens Enrollments.
  useEffect(()=>{ if(isAdmin && view==="enrollments") loadEnrollments(); },[isAdmin, view==="enrollments"]);
  useEffect(()=>{ if(!isAdmin) setEnrollments([]); },[isAdmin]);

  async function confirmDeleteEnrollment(){
    const {app,after}=deleteEnrollTarget;
    setReviewBusy(true);
    try{
      const res = await api("enrollments/"+app.id, {method:"DELETE"});
      if(!res.ok) throw new Error("delete failed");
      setEnrollments(list=>list.filter(a=>a.id!==app.id));
      if(after) after();
      setToast("Application deleted.");
    }catch(err){
      setToast("Couldn't delete the application. Check the server connection.");
    }finally{
      setReviewBusy(false);
      setDeleteEnrollTarget(null);
    }
  }

  async function loadNotifications(){
    if(!isAdmin) return;
    try{
      const res = await api("notifications");
      if(res.ok) setNotifItems(await res.json());
    }catch(err){} // The bell just keeps its last list.
  }
  // Admins only (viewers don't get a bell). Refreshed every minute.
  useEffect(()=>{
    if(!isAdmin){ setNotifItems([]); return; }
    loadNotifications();
    const t = setInterval(loadNotifications, 60000);
    return ()=>clearInterval(t);
  },[role]);
  const isNewer = (time,seen) => !seen || new Date(time) > new Date(seen);
  const notifications = notifItems.map(n=>({...n, unread: isNewer(n.time, notifOpen ? panelSeen : notifSeen)}));
  function handleNotifOpen(){
    setPanelSeen(notifSeen);
    const newest = notifItems[0] && notifItems[0].time;
    if(newest){ setNotifSeen(newest); writeNotifSeen(newest); }
  }
  function handleNotifClick(n){
    if(n.link.type==="entries" || n.link.type==="enrollments"){ if(isAdmin) changeView(n.link.type); return; }
    const list = n.link.type==="professional" ? professionals : students;
    if(list.some(p=>p.id===n.link.id)) openDetail(n.link.type, n.link.id);
    else setToast("This profile is no longer in the directory.");
  }

  async function confirmReview(){
    const {entry,decision}=reviewTarget;
    setReviewBusy(true);
    try{
      const res = await api("entries/"+entry.id+"/"+decision, {method:"POST"});
      const body = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(body.message || "review failed");
      setEntries(list=>list.map(e=>e.id===body.entry.id ? body.entry : e));
      if(body.profile){
        if(entry.type==="professional") setProfessionals(list=>[body.profile,...list]);
        else setStudents(list=>[body.profile,...list]);
      }
      loadNotifications();
      setToast(decision==="approve"
        ? `${entry.name} was added to ${entry.type==="professional"?"Working Professionals":"Students"}.`
        : `${entry.name}'s entry was rejected.`);
    }catch(err){
      setToast(err.message && err.message!=="review failed" ? err.message : "Couldn't update the entry. Check the server connection.");
    }finally{
      setReviewBusy(false);
      setReviewTarget(null);
    }
  }

  async function confirmDeleteEntry(){
    const {entry,after}=deleteEntryTarget;
    setReviewBusy(true);
    try{
      const res = await api("entries/"+entry.id, {method:"DELETE"});
      if(!res.ok) throw new Error("delete failed");
      setEntries(list=>list.filter(e=>e.id!==entry.id));
      if(after) after();
      loadNotifications();
      setToast("Entry deleted.");
    }catch(err){
      setToast("Couldn't delete the entry. Check the server connection.");
    }finally{
      setReviewBusy(false);
      setDeleteEntryTarget(null);
    }
  }
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

  // The account's role decides admin or viewer.
  async function handleLoginSubmit(email,password){
    try{
      const res = await api("auth/login", {
        method:"POST",
        body: JSON.stringify({email,password}),
      });
      const data = await res.json().catch(()=>({}));
      if(!res.ok) return {success:false, error: data.message || "Incorrect email or password."};
      setRestNonce(data.nonce);
      setAdminUser({name:data.name, email:data.email});
      setRole(data.admin ? "admin" : "viewer");
      if(data.admin && LINKED_VIEW) changeView(LINKED_VIEW);
      setToast("Signed in as "+data.name.split(" ")[0]+".");
      return {success:true};
    }catch(err){
      return {success:false, error:"Couldn't reach the server. Please try again."};
    }
  }
  // Sign out completely and go back to the home page.
  async function handleSignOut(){
    try{ await api("auth/logout", {method:"POST"}); }catch(err){}
    window.location.href = CONFIG.homeUrl;
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
        loadNotifications();
        setToast("Profile added successfully.");
      } else {
        const res = await api(endpoint+"/"+data.id, {
          method:"PUT", body: JSON.stringify(data),
        });
        if(!res.ok) throw new Error("update failed");
        const record = await res.json();
        if(type==="professional") setProfessionals(list=>list.map(p=>p.id===record.id?record:p));
        else setStudents(list=>list.map(s=>s.id===record.id?record:s));
        loadNotifications();
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
      loadNotifications();
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
    mainContent = <Dashboard professionals={professionals} students={students} setView={changeView} openDetail={openDetail} isAdmin={isAdmin} pendingCount={pendingCount}/>;
  } else if(view==="entries" && isAdmin){
    mainContent = <EntriesPage entries={entries} loading={entriesLoading} onReload={loadEntries}
      onReview={(entry,decision)=>setReviewTarget({entry,decision})}
      onDelete={(entry,after)=>setDeleteEntryTarget({entry,after})}
      openProfile={openDetail}/>;
  } else if(view==="enrollments" && isAdmin){
    mainContent = <EnrollmentsPage items={enrollments} loading={enrollLoading} onReload={loadEnrollments}
      onDelete={(app,after)=>setDeleteEnrollTarget({app,after})}/>;
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

  // Sign-in gate: nothing in the Talent Pool shows until an admin signs in.
  if(!signedIn){
    return (
      <div className="login-shell login-gate">
        <a className="login-gate-brand" href={CONFIG.homeUrl}><BrandLogo onDark/></a>
        <LoginModal onCancel={()=>{ window.location.href = CONFIG.homeUrl; }} onSubmit={handleLoginSubmit}/>
        <Toast message={toast} onClose={()=>setToast("")}/>
      </div>
    );
  }

  return (
    <div className={"app-shell"+(isAdmin?" with-sidebar":"")}>
      {isAdmin && <Sidebar view={view} setView={changeView} onSignOut={handleSignOut} pendingCount={pendingCount}/>}
      <div className="main-area">
        <TopHeader view={view} setView={changeView} globalSearch={globalSearch} setGlobalSearch={handleSearchChange} onSearchSubmit={handleSearchSubmit}
          notifOpen={notifOpen} setNotifOpen={setNotifOpen} profileOpen={profileOpen} setProfileOpen={setProfileOpen}
          notifications={notifications} onNotifOpen={handleNotifOpen} onNotifClick={handleNotifClick} isAdmin={isAdmin} adminUser={adminUser}
          onSignOut={handleSignOut}/>
        <main className="content">{mainContent}</main>
      </div>
      <MobileBottomNav view={view} setView={changeView} isAdmin={isAdmin}/>
      {deleteTarget && <DeleteModal name={deleteTarget.item.name} onCancel={()=>setDeleteTarget(null)} onConfirm={confirmDelete}/>}
      {reviewTarget && (reviewTarget.decision==="approve"
        ? <ConfirmModal tone="approve" title="Approve Entry?" busy={reviewBusy} confirmLabel="Approve & Add"
            message={<><strong>{reviewTarget.entry.name}</strong> will be added to the {reviewTarget.entry.type==="professional"?"Working Professionals":"Students"} list.</>}
            onCancel={()=>setReviewTarget(null)} onConfirm={confirmReview}/>
        : <ConfirmModal tone="reject" title="Reject Entry?" busy={reviewBusy} confirmLabel="Reject Entry"
            message={<><strong>{reviewTarget.entry.name}</strong>'s details won't be added to the directory. You can still see the entry under Rejected.</>}
            onCancel={()=>setReviewTarget(null)} onConfirm={confirmReview}/>)}
      {deleteEntryTarget && <ConfirmModal tone="reject" title="Delete Entry?" busy={reviewBusy} confirmLabel="Delete Entry"
        message={<><strong>{deleteEntryTarget.entry.name}</strong>'s entry will be removed from this list. Any profile created from it stays in the directory.</>}
        onCancel={()=>setDeleteEntryTarget(null)} onConfirm={confirmDeleteEntry}/>}
      {deleteEnrollTarget && <ConfirmModal tone="reject" title="Delete Application?" busy={reviewBusy} confirmLabel="Delete Application"
        message={<><strong>{deleteEnrollTarget.app.name}</strong>'s application will be removed from Enrollments.</>}
        onCancel={()=>setDeleteEnrollTarget(null)} onConfirm={confirmDeleteEnrollment}/>}
      <Toast message={toast} onClose={()=>setToast("")}/>
    </div>
  );
}

// Back/Forward can restore a frozen copy of this page from before the visitor signed in
// or out; load it fresh so the sign-in state is current.
// The public forms (Entry Form, Enroll Now) don't depend on sign-in.
const PUBLIC_VIEW = CONFIG.view==="entry" || CONFIG.view==="enroll";
window.addEventListener("pageshow", e=>{ if(e.persisted && !PUBLIC_VIEW) window.location.reload(); });

ReactDOM.createRoot(document.getElementById("root")).render(
  CONFIG.view==="entry" ? <EntryFormApp/> : CONFIG.view==="enroll" ? <EnrollFormApp/> : <App/>
);
