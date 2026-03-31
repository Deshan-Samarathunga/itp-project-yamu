import { Link } from 'react-router-dom';
import { FiUsers, FiDroplet, FiCalendar, FiSettings } from 'react-icons/fi';

export default function VehicleCard({ vehicle }) {
  const imgSrc = vehicle.images?.img1
    ? `http://localhost:5000/uploads/vehicles/${vehicle.images.img1}`
    : 'https://placehold.co/400x250/0d1b2a/f0a500?text=No+Image';

  return (
    <div className="card">
      <div className="card-img">
        <div className="card-tag"><span>{vehicle.vehicleBrand}</span></div>
        <img src={imgSrc} alt={vehicle.vehicleTitle} />
      </div>
      <div className="card-body">
        <h4>{vehicle.vehicleTitle}</h4>
        <p style={{ fontSize: '0.85rem', color: 'var(--text-light)' }}>{vehicle.year}</p>
        <div className="specs">
          <span><FiUsers /> {vehicle.capacity}</span>
          <span><FiDroplet /> {vehicle.fuelType}</span>
          <span><FiSettings /> {vehicle.transmission}</span>
          <span><FiCalendar /> {vehicle.year}</span>
        </div>
        <div className="price-row">
          <div className="price">Rs.{vehicle.price?.toLocaleString()} <span>/ Day</span></div>
          <Link to={`/cars/${vehicle._id}`} className="btn btn-primary btn-sm">View More</Link>
        </div>
      </div>
    </div>
  );
}
