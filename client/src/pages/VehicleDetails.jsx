import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import API from '../api/axios';
import { FiUsers, FiDroplet, FiCalendar, FiSettings, FiMapPin, FiCheck, FiX } from 'react-icons/fi';

export default function VehicleDetails() {
  const { id } = useParams();
  const { user } = useAuth();
  const navigate = useNavigate();
  const [vehicle, setVehicle] = useState(null);
  const [loading, setLoading] = useState(true);
  const [booking, setBooking] = useState({ startDate: '', endDate: '' });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    API.get(`/vehicles/${id}`)
      .then(r => setVehicle(r.data))
      .catch(() => setError('Vehicle not found'))
      .finally(() => setLoading(false));
  }, [id]);

  const calcDays = () => {
    if (!booking.startDate || !booking.endDate) return 0;
    const d = (new Date(booking.endDate) - new Date(booking.startDate)) / (1000 * 60 * 60 * 24);
    return d > 0 ? Math.ceil(d) : 0;
  };

  const handleBook = async (e) => {
    e.preventDefault();
    if (!user) return navigate('/signin');
    setSubmitting(true);
    setError('');
    try {
      await API.post('/bookings', { vehicleId: id, startDate: booking.startDate, endDate: booking.endDate });
      setSuccess('Booking created successfully! Check your bookings.');
      setBooking({ startDate: '', endDate: '' });
    } catch (err) {
      setError(err.response?.data?.message || 'Booking failed');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) return <div className="page-content"><div className="spinner-container"><div className="spinner"></div></div></div>;
  if (!vehicle) return <div className="page-content"><div className="empty-state"><h3>Vehicle not found</h3></div></div>;

  const imgs = [vehicle.images?.img1, vehicle.images?.img2, vehicle.images?.img3, vehicle.images?.img4].filter(Boolean);
  const featureList = [
    { key: 'airConditioner', label: 'Air Conditioner' },
    { key: 'powerDoorLocks', label: 'Power Door Locks' },
    { key: 'antiLockBrakingSystem', label: 'Anti-Lock Braking' },
    { key: 'brakeAssist', label: 'Brake Assist' },
    { key: 'powerSteering', label: 'Power Steering' },
    { key: 'driverAirbag', label: 'Driver Airbag' },
    { key: 'passengerAirbag', label: 'Passenger Airbag' },
    { key: 'powerWindows', label: 'Power Windows' },
    { key: 'cdPlayer', label: 'CD Player' },
  ];
  const days = calcDays();

  return (
    <div className="page-content">
      <div className="page-header">
        <h1>{vehicle.vehicleTitle}</h1>
        <p>{vehicle.vehicleBrand} • {vehicle.year} • {vehicle.location}</p>
      </div>
      <section className="vehicle-detail">
        <div className="container">
          <div className="row" style={{ alignItems: 'flex-start' }}>
            <div style={{ flex: 2 }}>
              {/* Gallery */}
              <div className="vehicle-gallery">
                {imgs.length > 0 ? imgs.map((img, i) => (
                  <img key={i} src={`http://localhost:5000/uploads/vehicles/${img}`} alt={`${vehicle.vehicleTitle} ${i + 1}`} />
                )) : (
                  <img src="https://placehold.co/800x400/0d1b2a/f0a500?text=No+Image" alt="No image" />
                )}
              </div>
              {/* Info */}
              <div className="vehicle-info" style={{ marginTop: '2rem' }}>
                <h1>{vehicle.vehicleTitle}</h1>
                <div className="price-tag">Rs.{vehicle.price?.toLocaleString()} <span>/ Day</span></div>
                <div className="vehicle-specs">
                  <div className="spec-item"><FiUsers /> {vehicle.capacity} Passengers</div>
                  <div className="spec-item"><FiDroplet /> {vehicle.fuelType}</div>
                  <div className="spec-item"><FiSettings /> {vehicle.transmission}</div>
                  <div className="spec-item"><FiCalendar /> {vehicle.year}</div>
                  <div className="spec-item"><FiMapPin /> {vehicle.location}</div>
                  <div className="spec-item"><FiSettings /> {vehicle.engineCapacity || 'N/A'} CC</div>
                </div>
                {vehicle.vehicleDesc && (
                  <div style={{ marginTop: '1.5rem' }}>
                    <h3 style={{ marginBottom: '0.75rem' }}>Description</h3>
                    <p style={{ color: 'var(--text-light)', lineHeight: 1.8 }}>{vehicle.vehicleDesc}</p>
                  </div>
                )}
                <div style={{ marginTop: '1.5rem' }}>
                  <h3 style={{ marginBottom: '0.75rem' }}>Features</h3>
                  <div className="vehicle-features">
                    {featureList.map(f => (
                      <div key={f.key} className={`feature-item ${vehicle.features?.[f.key] ? 'active' : 'inactive'}`}>
                        {vehicle.features?.[f.key] ? <FiCheck /> : <FiX />} {f.label}
                      </div>
                    ))}
                  </div>
                </div>
                {vehicle.owner && (
                  <div style={{ marginTop: '2rem', padding: '1.5rem', background: 'var(--gray-50)', borderRadius: 'var(--radius)' }}>
                    <h3 style={{ marginBottom: '0.75rem' }}>Listed By</h3>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                      <img src={`https://ui-avatars.com/api/?name=${encodeURIComponent(vehicle.owner.fullName)}&background=f0a500&color=0d1b2a&bold=true&size=48`} alt="" style={{ width: 48, height: 48, borderRadius: '50%' }} />
                      <div>
                        <h4>{vehicle.owner.fullName}</h4>
                        <p style={{ color: 'var(--text-light)', fontSize: '0.9rem' }}>{vehicle.owner.city} • {vehicle.owner.phone}</p>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Booking Card */}
            <div style={{ flex: 1, minWidth: 320 }}>
              <div className="booking-card">
                <h3>Book This Vehicle</h3>
                {error && <div className="alert alert-danger">{error}</div>}
                {success && <div className="alert alert-success">{success}</div>}
                <form onSubmit={handleBook}>
                  <div className="form-group">
                    <label>Pick-up Date</label>
                    <input type="date" value={booking.startDate}
                      min={new Date().toISOString().split('T')[0]}
                      onChange={e => setBooking(p => ({ ...p, startDate: e.target.value }))} required />
                  </div>
                  <div className="form-group">
                    <label>Return Date</label>
                    <input type="date" value={booking.endDate}
                      min={booking.startDate || new Date().toISOString().split('T')[0]}
                      onChange={e => setBooking(p => ({ ...p, endDate: e.target.value }))} required />
                  </div>
                  {days > 0 && (
                    <div style={{ padding: '1rem', background: 'var(--gray-50)', borderRadius: 'var(--radius-sm)', marginBottom: '1rem' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem' }}>
                        <span>Rs.{vehicle.price?.toLocaleString()} × {days} days</span>
                        <span>Rs.{(vehicle.price * days).toLocaleString()}</span>
                      </div>
                      <hr style={{ border: 'none', borderTop: '1px solid var(--border)', margin: '0.5rem 0' }} />
                      <div style={{ display: 'flex', justifyContent: 'space-between', fontWeight: 700, fontSize: '1.1rem' }}>
                        <span>Total</span>
                        <span style={{ color: 'var(--accent)' }}>Rs.{(vehicle.price * days).toLocaleString()}</span>
                      </div>
                    </div>
                  )}
                  <button type="submit" className="btn btn-primary btn-block" disabled={submitting}>
                    {submitting ? 'Booking...' : user ? 'Book Now' : 'Sign In to Book'}
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
