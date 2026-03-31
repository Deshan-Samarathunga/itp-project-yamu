import Sidebar from '../../components/Sidebar';
export default function DriverReviews() {
  return (<div className="dashboard-layout"><Sidebar /><main className="dashboard-content"><div className="form-header"><h2>Reviews</h2></div><div className="empty-state"><h3>No reviews yet</h3><p>Reviews from your customers will appear here.</p></div></main></div>);
}
