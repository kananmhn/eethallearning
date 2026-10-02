// "Export CSV" on the Enrollments page. Column titles are the questions as worded on the form.
import { formatDate } from "../shared.jsx";
import { FIELDS, label } from "./texts.js";

export function downloadCsv(rows,filename){
  const columns = [...FIELDS.map(f=>[f,label(f)]), ["batch","Batch"], ["createdAt","Submitted"]];
  const cell = v => { const s=String(v==null?"":v); return /[",\n]/.test(s) ? '"'+s.replace(/"/g,'""')+'"' : s; };
  const lines = [columns.map(c=>cell(c[1])).join(",")]
    .concat(rows.map(r=>columns.map(([k])=>cell(k==="createdAt" ? formatDate(r[k]) : r[k])).join(",")));
  // The BOM makes Excel read the file as UTF-8.
  const url = URL.createObjectURL(new Blob([String.fromCharCode(0xFEFF)+lines.join("\r\n")],{type:"text/csv;charset=utf-8"}));
  const a = document.createElement("a");
  a.href=url; a.download=filename; document.body.appendChild(a); a.click(); a.remove();
  setTimeout(()=>URL.revokeObjectURL(url),1000);
}
