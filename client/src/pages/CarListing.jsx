import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import VehicleCard from '../components/VehicleCard';
import API from '../api/axios';

export default function CarListing() {
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchParams, setSearchParams] = useSearchParams();
  const [filters, setFilters] = useState({
    brand: searchParams.get('brand') || '',
    transmission: searchParams.get('transmission') || '',
    fuelType: searchParams.get('fuelType') || '',
    location: searchParams.get('location') || '',
    search: searchParams.get('search') || '',
  });

  useEffect(() => {
    setLoading(true);
    const params = {};
    Object.entries(filters).forEach(([k, v]) => { if (v) params[k] = v; });
    API.get('/vehicles', { params })
      .then(r => setVehicles(r.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [filters]);

  const onFilter = (key, val) => {
    const next = { ...filters, [key]: val };
    setFilters(next);
    const p = new URLSearchParams();
    Object.entries(next).forEach(([k, v]) => { if (v) p.set(k, v); });
    setSearchParams(p);
  };

  return (
    <div className="page-content">
      <div className="page-header">
        <h1>Browse Cars</h1>
        <p>Find the perfect vehicle for your journey</p>
      </div>
      <div className="container" style={{ padding: '2rem 1.5rem' }}>
        <div className="filter-bar">
          <input
            placeholder="Search vehicles..."
            value={filters.search}
            onChange={e => onFilter('search', e.target.value)}
          />
          <select value={filters.brand} onChange={e => onFilter('brand', e.target.value)}>
            <option value="">All Brands</option>
            {['Toyota', 'Suzuki', 'Nissan', 'BMW', 'Audi', 'Benz', 'Ford', 'KIA', 'Tesla', 'Volkswagen', 'Mitsubishi', 'Peugeot'].map(b =>
              <option key={b} value={b}>{b}</option>
            )}
          </select>
          <select value={filters.transmission} onChange={e => onFilter('transmission', e.target.value)}>
            <option value="">All Transmissions</option>
            <option value="Automatic">Automatic</option>
            <option value="Manual">Manual</option>
          </select>
          <select value={filters.fuelType} onChange={e => onFilter('fuelType', e.target.value)}>
            <option value="">All Fuel Types</option>
            <option value="Petrol">Petrol</option>
            <option value="Diesel">Diesel</option>
            <option value="Electric">Electric</option>
            <option value="Hybrid">Hybrid</option>
          </select>
          <input
            placeholder="Location..."
            value={filters.location}
            onChange={e => onFilter('location', e.target.value)}
          />
        </div>

        {loading ? (
          <div className="spinner-container"><div className="spinner"></div></div>
        ) : vehicles.length > 0 ? (
          <div className="grid-3">{vehicles.map(v => <VehicleCard key={v._id} vehicle={v} />)}</div>
        ) : (
          <div className="empty-state">
            <h3>No vehicles found</h3>
            <p>Try adjusting your filters</p>
          </div>
        )}
      </div>
    </div>
  );
}
