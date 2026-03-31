import { FiStar } from 'react-icons/fi';

export default function ReviewCard({ review }) {
  return (
    <div className="card" style={{ padding: '1.25rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '0.75rem' }}>
        <img
          src={'https://ui-avatars.com/api/?name=' + encodeURIComponent(review.reviewer?.fullName || 'User') + '&background=f0a500&color=0d1b2a&bold=true&size=40'}
          alt="reviewer"
          style={{ width: 40, height: 40, borderRadius: '50%' }}
        />
        <div>
          <h4 style={{ fontSize: '0.95rem' }}>{review.reviewer?.fullName || 'Anonymous'}</h4>
          <div className="stars">
            {[1, 2, 3, 4, 5].map(s => (
              <FiStar key={s} className={`star ${s <= review.rating ? 'filled' : ''}`}
                style={s <= review.rating ? { fill: 'var(--accent)', color: 'var(--accent)' } : {}} />
            ))}
          </div>
        </div>
      </div>
      <p style={{ color: 'var(--text-light)', fontSize: '0.9rem' }}>{review.comment}</p>
    </div>
  );
}
