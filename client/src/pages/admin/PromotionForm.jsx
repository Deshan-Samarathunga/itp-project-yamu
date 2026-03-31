import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function PromotionForm() {
  const {id}=useParams(); const navigate=useNavigate();const isEdit=!!id;
  const [form, setForm] = useState({code:'',discount:'',discountType:'percentage',description:'',validFrom:'',validUntil:'',minAmount:'',maxUses:''});
  const [loading,setLoading]=useState(false);const [error,setError]=useState('');

  useEffect(()=>{if(isEdit){API.get(`/promotions/${id}`).then(r=>{const d=r.data;setForm({code:d.code||'',discount:d.discount||'',discountType:d.discountType||'percentage',description:d.description||'',validFrom:d.validFrom?.slice(0,10)||'',validUntil:d.validUntil?.slice(0,10)||'',minAmount:d.minAmount||'',maxUses:d.maxUses||''})}).catch(()=>{})}},[id]);

  const handleSubmit=async(e)=>{e.preventDefault();setLoading(true);setError('');
    const data={...form,discount:Number(form.discount),minAmount:Number(form.minAmount)||0,maxUses:Number(form.maxUses)||0};
    try{if(isEdit)await API.put(`/promotions/${id}`,data);else await API.post('/promotions',data);navigate('/admin/promotions');}
    catch(err){setError(err.response?.data?.message||'Failed');}finally{setLoading(false);}};

  return (
    <div className="dashboard-layout"><Sidebar /><main className="dashboard-content">
      <div className="form-card" style={{maxWidth:600}}>
        <div className="form-header"><h2>{isEdit?'Edit Promotion':'New Promotion'}</h2></div>
        {error&&<div className="alert alert-danger">{error}</div>}
        <form onSubmit={handleSubmit}>
          <div className="form-row">
            <div className="form-group"><label>Code</label><input value={form.code} onChange={e=>setForm(p=>({...p,code:e.target.value.toUpperCase()}))} required /></div>
            <div className="form-group"><label>Discount Type</label><select value={form.discountType} onChange={e=>setForm(p=>({...p,discountType:e.target.value}))}><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>Discount Value</label><input type="number" value={form.discount} onChange={e=>setForm(p=>({...p,discount:e.target.value}))} required /></div>
            <div className="form-group"><label>Min Booking Amount</label><input type="number" value={form.minAmount} onChange={e=>setForm(p=>({...p,minAmount:e.target.value}))} /></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>Valid From</label><input type="date" value={form.validFrom} onChange={e=>setForm(p=>({...p,validFrom:e.target.value}))} /></div>
            <div className="form-group"><label>Valid Until</label><input type="date" value={form.validUntil} onChange={e=>setForm(p=>({...p,validUntil:e.target.value}))} /></div>
          </div>
          <div className="form-group"><label>Max Uses</label><input type="number" value={form.maxUses} onChange={e=>setForm(p=>({...p,maxUses:e.target.value}))} /></div>
          <div className="form-group"><label>Description</label><textarea rows={3} value={form.description} onChange={e=>setForm(p=>({...p,description:e.target.value}))} /></div>
          <button type="submit" className="btn btn-primary" disabled={loading}>{loading?'Saving...':isEdit?'Update':'Create'}</button>
        </form>
      </div>
    </main></div>
  );
}
