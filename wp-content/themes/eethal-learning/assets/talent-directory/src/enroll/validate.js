// Checks before sending. The server repeats them (eethal_enroll_clean() in inc/enrollments.php),
// so keep the two in step.
import { label, todayIso } from "./texts.js";

export function validateEnroll(d){
  const e={};
  const required = f => `${label(f)} is required.`;
  ["name","referredBy","district","college"].forEach(f=>{ if(!d[f].trim()) e[f]=required(f); });
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email.trim())) e.email="Enter a valid email address.";
  if(!/^\d{10}$/.test(d.mobile)) e.mobile="Enter a valid 10-digit mobile number.";
  if(!d.status) e.status="Choose one of the options.";
  if(!d.degree.trim()) e.degree = d.degreeChoice==="other" ? "Type your degree." : required("degree");
  const y = parseInt(d.passedOut,10);
  if(!/^\d{4}$/.test(d.passedOut) || y<1970 || y>new Date().getFullYear()+6) e.passedOut="Enter the year as 4 digits, e.g. 2024.";
  if(!d.dob || d.dob>todayIso()) e.dob="Enter a valid date of birth.";
  return e;
}
