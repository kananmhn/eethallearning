// Admin "Enrollments" view: the applications from the Enroll Now form, with filters,
// a detail view and CSV export. Data loading and deleting are handled by App (app.jsx).
import { CONFIG } from "../core.js";
import { I } from "../icons.jsx";
import { Avatar, EmptyState, FilterSelect, DetailField, sameText, uniqueOptions, formatDate } from "../shared.jsx";
import { ENROLL, FIELDS, label, todayIso } from "./texts.js";
import { downloadCsv } from "./csv.js";

const { useState, useMemo } = React;

function formatDob(iso){
  if(!iso) return "";
  const [y,m,d]=iso.split("-").map(Number);
  return new Date(y,m-1,d).toLocaleDateString("en-IN",{day:"numeric",month:"short",year:"numeric"});
}
// A field's value as shown in the detail view.
function shown(app,field){
  if(field==="mobile") return "+91 "+app.mobile;
  if(field==="dob") return formatDob(app.dob);
  return app[field];
}

function EnrollmentDetail({app,onBack,onDelete}){
  const half = Math.ceil(FIELDS.length/2);
  return (
    <div>
      <div className="detail-top">
        <button className="back-btn" onClick={onBack}><I.Back/> Back to Enrollments</button>
        <button className="btn btn-secondary btn-sm" style={{color:'var(--red)'}} onClick={()=>onDelete(app)}><I.Trash/> Delete</button>
      </div>
      <div className="profile-header-card">
        <Avatar name={app.name} size={110}/>
        <div>
          <div className="ph-name-row">
            <h2 className="font-display">{app.name}</h2>
            <span className="status-chip status-chip--approved">{app.batch}</span>
          </div>
          <div className="ph-role">{app.status}</div>
          <div className="ph-contacts">
            <div className="ph-contact-item"><I.Pin/> {app.district}</div>
            <div className="ph-contact-item"><I.Mail/> {app.email}</div>
            <div className="ph-contact-item"><I.Phone/> +91 {app.mobile}</div>
            <div className="ph-contact-item"><I.Clock/> Applied {formatDate(app.createdAt)}</div>
          </div>
        </div>
      </div>
      <div className="section-card">
        <div className="section-head"><h3>Application Details</h3></div>
        <div className="detail-grid">
          {[FIELDS.slice(0,half), FIELDS.slice(half)].map((col,i)=>(
            <div key={i}>{col.map(f=><DetailField key={f} label={label(f)} value={shown(app,f)}/>)}</div>
          ))}
        </div>
      </div>
    </div>
  );
}

const NO_FILTERS = {batch:"",status:"",degree:"",district:""};

export function EnrollmentsPage({items,loading,onReload,onDelete}){
  const [search,setSearch]=useState("");
  const [f,setF]=useState(NO_FILTERS);
  const [selectedId,setSelectedId]=useState(null);
  // Options come from the applications, so older wording stays filterable after the form changes.
  const opts = useMemo(()=>({
    batches: uniqueOptions(items.map(a=>a.batch)),
    statuses: uniqueOptions(items.map(a=>a.status)),
    degrees: uniqueOptions(items.map(a=>a.degree)),
    districts: uniqueOptions(items.map(a=>a.district)),
  }),[items]);
  const list = useMemo(()=>{
    const s = search.trim().toLowerCase();
    return items.filter(a=>
      (!s || [a.name,a.email,a.mobile,a.college,a.referredBy].some(v=>(v||"").toLowerCase().includes(s))) &&
      (!f.batch || sameText(a.batch,f.batch)) && (!f.status || sameText(a.status,f.status)) &&
      (!f.degree || sameText(a.degree,f.degree)) && (!f.district || sameText(a.district,f.district)));
  },[items,search,f]);
  const selected = items.find(a=>a.id===selectedId);
  const reset = ()=>{ setSearch(""); setF(NO_FILTERS); };

  if(selected) return <EnrollmentDetail app={selected} onBack={()=>setSelectedId(null)} onDelete={a=>onDelete(a,()=>setSelectedId(null))}/>;

  return (
    <div>
      <div className="page-head">
        <div><h1 className="font-display">Enrollments</h1><p>Applications submitted through the Enroll Now form. New ones join <strong>{ENROLL.batch}</strong>.</p></div>
        <div style={{display:'flex',gap:8,flexWrap:'wrap'}}>
          <button className="btn btn-secondary" onClick={onReload} disabled={loading}>{loading ? "Refreshing..." : "Refresh"}</button>
          <button className="btn btn-secondary" disabled={!list.length} onClick={()=>downloadCsv(list,"enrollments-"+todayIso()+".csv")}><I.Download/> Export CSV</button>
          {CONFIG.pages.enroll && <a className="btn btn-primary" href={CONFIG.pages.enroll} target="_blank" rel="noopener">Open Enroll Form <I.Arrow/></a>}
        </div>
      </div>

      <div className="filter-bar">
        <div className="filter-search">
          <I.Search/>
          <input placeholder="Search by name, email, mobile, college..." value={search} onChange={e=>setSearch(e.target.value)}/>
        </div>
        <div className="filter-row">
          <FilterSelect value={f.batch} onChange={v=>setF({...f,batch:v})} options={opts.batches} placeholder="Batch"/>
          <FilterSelect value={f.status} onChange={v=>setF({...f,status:v})} options={opts.statuses} placeholder={label("status")}/>
          <FilterSelect value={f.degree} onChange={v=>setF({...f,degree:v})} options={opts.degrees} placeholder={label("degree")}/>
          <FilterSelect value={f.district} onChange={v=>setF({...f,district:v})} options={opts.districts} placeholder={label("district")}/>
          <button className="btn btn-secondary btn-sm" onClick={reset}>Reset</button>
        </div>
      </div>

      {items.length===0 ? (
        <EmptyState title={loading ? "Loading applications..." : "No applications yet"} subtitle="Applications from the Enroll Now form will show up here."/>
      ) : list.length===0 ? (
        <EmptyState title="No applications found" subtitle="Try changing your search or filters." actionLabel="Clear Filters" onAction={reset}/>
      ) : (
        <>
        <div className="enroll-count">{list.length} of {items.length} application{items.length===1?"":"s"}</div>
        <div className="entry-list">
          {list.map(a=>(
            <div key={a.id} role="button" tabIndex="0" className="entry-row entry-row--enroll" onClick={()=>setSelectedId(a.id)}
              onKeyDown={ev=>{ if(ev.target===ev.currentTarget && (ev.key==="Enter"||ev.key===" ")){ ev.preventDefault(); setSelectedId(a.id); } }}>
              <Avatar name={a.name} size={46}/>
              <div className="entry-row-main">
                <div className="entry-row-name">{a.name} <span className="entry-dup enroll-status" title={a.status}>{a.status}</span></div>
                <div className="entry-row-meta">
                  <span><I.Mail/> {a.email}</span>
                  <span><I.Phone/> +91 {a.mobile}</span>
                  <span><I.Pin/> {a.district}</span>
                  <span><I.Grad/> {a.degree} &middot; {a.passedOut}</span>
                </div>
              </div>
              <div className="entry-row-side">
                <span className="status-chip status-chip--approved">{a.batch}</span>
                <span className="entry-row-date"><I.Clock/> {formatDate(a.createdAt)}</span>
              </div>
            </div>
          ))}
        </div>
        </>
      )}
    </div>
  );
}
