// Enroll Now wording. It is authored in WordPress (Customizer → Eethal Front Page →
// Enroll Now Form) and passed in as EETHAL_TD.enroll by eethal_enroll_texts().
import { CONFIG } from "../core.js";

export const ENROLL = CONFIG.enroll || {
  batch:"", badge:"", title:"Enroll Now", intro:"", note:"", sections:["",""], questions:{},
  statuses:[], degrees:[], degreeOther:"", button:"Submit", thanksTitle:"Thank you!", thanksText:"",
};

// The form's fields, in the order the admin list, detail view and CSV show them.
export const FIELDS = ["name","email","mobile","referredBy","status","degree","passedOut","dob","district","college"];

// Label of a question, e.g. label("referredBy") => "Referred by".
export const label = field => (ENROLL.questions[field] && ENROLL.questions[field].label) || field;
export const hint = field => (ENROLL.questions[field] && ENROLL.questions[field].hint) || "";

// "Thank you, {name}!" => "Thank you, Priya!"
export const fill = (text, values) => String(text||"").replace(/\{(\w+)\}/g, (m,k)=> k in values ? values[k] : m);

export const todayIso = () => new Date().toISOString().slice(0,10);
