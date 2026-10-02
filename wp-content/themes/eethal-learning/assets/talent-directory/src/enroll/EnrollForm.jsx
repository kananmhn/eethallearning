// The application form itself. Questions and choices come from WordPress (texts.js).
import { api } from "../core.js";
import { I } from "../icons.jsx";
import { Field } from "../shared.jsx";
import { ENROLL, label, hint, todayIso } from "./texts.js";
import { validateEnroll } from "./validate.js";

const { useState } = React;

const BLANK = {name:"",email:"",mobile:"",referredBy:"",status:"",degreeChoice:"",degree:"",passedOut:"",dob:"",district:"",college:"",website:""};

// A row of click-to-select options (a radio group).
function Choices({name,options,value,onChange,row}){
  return (
    <div className={"choice-list"+(row?" choice-list--row":"")} role="radiogroup" aria-label={name}>
      {options.map(o=>(
        <button key={o.value} type="button" role="radio" aria-checked={value===o.value} className={"choice"+(value===o.value?" active":"")} onClick={()=>onChange(o.value)}>
          <span className="choice-dot"/> {o.label}
        </button>
      ))}
    </div>
  );
}

export function EnrollForm({onSubmitted}){
  const [d,setD]=useState(BLANK);
  const [errors,setErrors]=useState({});
  const [busy,setBusy]=useState(false);
  const [formError,setFormError]=useState("");

  const clearError = k => setErrors(x=>{ if(!x[k]) return x; const n={...x}; delete n[k]; return n; });
  const update = (k,v) => { setD(x=>({...x,[k]:v})); clearError(k); };
  function chooseDegree(choice){
    setD(x=>({...x, degreeChoice:choice, degree: choice==="other" ? "" : choice}));
    clearError("degree");
  }
  // A text question: label and hint as authored in WordPress.
  const text = (field, props={}) => (
    <Field errors={errors} field={field} label={label(field)} required>
      <input value={d[field]} placeholder={hint(field)} onChange={e=>update(field,e.target.value)} {...props}/>
    </Field>
  );

  async function submit(ev){
    ev.preventDefault();
    if(busy) return;
    setFormError("");
    const e = validateEnroll(d);
    setErrors(e);
    if(Object.keys(e).length){ setFormError("Please correct the highlighted fields."); return; }
    setBusy(true);
    try{
      const {degreeChoice, ...body} = d;
      const res = await api("enrollments", {method:"POST", body: JSON.stringify(body)});
      const out = await res.json().catch(()=>({}));
      if(!res.ok){
        if(out.data && out.data.fields) setErrors(out.data.fields);
        setFormError(out.message || "Couldn't submit the form. Please try again.");
        return;
      }
      onSubmitted(d.name);
    }catch(err){
      setFormError("Couldn't reach the server. Please try again.");
    }finally{
      setBusy(false);
    }
  }

  const degreeOptions = ENROLL.degrees.map(g=>({value:g,label:g}));
  if(ENROLL.degreeOther) degreeOptions.push({value:"other",label:ENROLL.degreeOther});

  return (
    <form className="section-card enroll-form" onSubmit={submit} noValidate>
      {ENROLL.sections[0] && <div className="form-section-title">{ENROLL.sections[0]}</div>}
      <div className="form-grid">
        {text("name", {autoComplete:"name"})}
        {text("email", {type:"email", autoComplete:"email"})}
        {text("mobile", {inputMode:"numeric", autoComplete:"tel-national", onChange:e=>update("mobile",e.target.value.replace(/\D/g,"").slice(0,10))})}
        {text("dob", {type:"date", max:todayIso()})}
        {text("district")}
        {text("referredBy")}
        <Field errors={errors} field="status" label={label("status")} required span>
          <Choices name={label("status")} options={ENROLL.statuses.map(s=>({value:s,label:s}))} value={d.status} onChange={v=>update("status",v)}/>
        </Field>
      </div>

      {ENROLL.sections[1] && <div className="form-section-title">{ENROLL.sections[1]}</div>}
      <div className="form-grid">
        <Field errors={errors} field="degree" label={label("degree")} required span>
          <Choices row name={label("degree")} options={degreeOptions} value={d.degreeChoice} onChange={chooseDegree}/>
          {d.degreeChoice==="other" && <input className="choice-other" autoFocus value={d.degree} placeholder={hint("degree")} onChange={e=>update("degree",e.target.value)}/>}
        </Field>
        {text("college")}
        {text("passedOut", {inputMode:"numeric", onChange:e=>update("passedOut",e.target.value.replace(/\D/g,"").slice(0,4))})}
        {/* Left empty by people; bots fill it in. */}
        <input className="hp-field" tabIndex="-1" autoComplete="off" aria-hidden="true" value={d.website} onChange={e=>update("website",e.target.value)}/>
      </div>

      {formError && <div className="form-error-banner"><I.Alert/> {formError}</div>}
      <div className="form-actions">
        <button type="submit" className="btn btn-primary" disabled={busy}><I.Send/> {busy ? "Submitting..." : ENROLL.button}</button>
      </div>
    </form>
  );
}
