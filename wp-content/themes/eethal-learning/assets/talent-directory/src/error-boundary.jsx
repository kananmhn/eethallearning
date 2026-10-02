// Shows what went wrong instead of a silent blank or stuck screen when part of the app
// crashes, so the message can be reported. Wraps the whole app in app.jsx.
import { I } from "./icons.jsx";

export class ErrorBoundary extends React.Component {
  constructor(props){ super(props); this.state = {error:null}; }
  static getDerivedStateFromError(error){ return {error}; }
  componentDidCatch(error, info){ console.error("Talent Pool crashed:", error, info && info.componentStack); }
  render(){
    const {error} = this.state;
    if(!error) return this.props.children;
    return (
      <div className="login-shell login-gate">
        <div className="modal-overlay is-locked">
          <div className="modal-card">
            <div className="modal-icon" style={{background:'var(--red-soft)',color:'var(--red)'}}><I.Alert/></div>
            <h3>Something went wrong</h3>
            <p>Please reload the page. If this keeps happening, send this message to the site admin:</p>
            <pre className="crash-message">{String((error && (error.stack || error.message)) || error).slice(0, 600)}</pre>
            <div className="modal-actions">
              <button className="btn btn-primary" onClick={()=>window.location.reload()}>Reload</button>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
