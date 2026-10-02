// The public Enroll Now page (/enroll/): hero, form, and the thank-you screen.
import { CONFIG } from "../core.js";
import { I } from "../icons.jsx";
import { BrandLogo } from "../shared.jsx";
import { ENROLL, fill } from "./texts.js";
import { EnrollForm } from "./EnrollForm.jsx";

const { useState } = React;

export function EnrollFormApp(){
  const [submitted,setSubmitted]=useState(""); // applicant's name once sent
  const [formKey,setFormKey]=useState(0);
  return (
    <div className="entry-page">
      <header className="entry-topbar">
        <a className="brand-row" href={CONFIG.homeUrl}><BrandLogo/></a>
      </header>

      <section className="entry-hero">
        {ENROLL.badge && <span className="entry-hero-pill"><I.Grad style={{width:14,height:14}}/> {ENROLL.badge}</span>}
        <h1 className="font-display">{ENROLL.title}</h1>
        {ENROLL.intro && <p className="entry-hero-text">{ENROLL.intro}</p>}
        {ENROLL.note && <p className="entry-hero-text entry-hero-sub">{ENROLL.note}</p>}
      </section>

      <main className="entry-main">
        {submitted ? (
          <div className="section-card entry-success">
            <div className="entry-success-ic"><I.Check/></div>
            <h2 className="font-display">{fill(ENROLL.thanksTitle, {name: submitted.split(" ")[0]})}</h2>
            {ENROLL.thanksText && <p className="entry-hero-text">{fill(ENROLL.thanksText, {name: submitted.split(" ")[0]})}</p>}
            <div style={{display:'flex',gap:10,justifyContent:'center',flexWrap:'wrap'}}>
              <a className="btn btn-primary" href={CONFIG.homeUrl}>Back to Home</a>
              <button className="btn btn-secondary" onClick={()=>{setSubmitted("");setFormKey(k=>k+1);}}><I.Plus/> Submit Another</button>
            </div>
          </div>
        ) : (
          <EnrollForm key={formKey} onSubmitted={name=>{ setSubmitted(name); window.scrollTo({top:0, behavior:"smooth"}); }}/>
        )}
      </main>
    </div>
  );
}
