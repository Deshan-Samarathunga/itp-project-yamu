import Sidebar from '../../components/Sidebar';
export default function AdminDisputes() {
  return (<div className="dashboard-layout"><Sidebar /><main className="dashboard-content"><div className="form-header"><h2>Disputes</h2></div><div className="empty-state"><h3>No disputes</h3><p>Dispute management will show cases here.</p></div></main></div>);
}
