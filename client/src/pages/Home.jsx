import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import VehicleCard from '../components/VehicleCard';
import API from '../api/axios';
import { FiArrowRight, FiMapPin, FiPhone, FiMail } from 'react-icons/fi';
import { RiDoubleQuotesL } from 'react-icons/ri';

export default function Home() {
  const { user } = useAuth();
  const [vehicles, setVehicles] = useState([]);
  const [activeTab, setActiveTab] = useState('s1');

  useEffect(() => {
    API.get('/vehicles').then(r => setVehicles(r.data.slice(0, 6))).catch(() => {});
  }, []);

  const getDashLink = () => {
    if (!user) return { href: '/cars', text: 'Book Ride', href2: '/cars', text2: 'Explore Cars' };
    switch (user.role) {
      case 'admin': return { href: '/admin/dashboard', text: 'Admin Dashboard', href2: '/cars', text2: 'Explore Cars' };
      case 'driver': return { href: '/driver/dashboard', text: 'Driver Dashboard', href2: '/driver/ads', text2: 'My Tour Ads' };
      case 'staff': return { href: '/staff/dashboard', text: 'Staff Dashboard', href2: '/cars', text2: 'Explore Cars' };
      default: return { href: '/customer/bookings', text: 'My Bookings', href2: '/cars', text2: 'Explore Cars' };
    }
  };
  const dash = getDashLink();

  const services = [
    { id: 's1', num: '01', title: 'Mobile App', desc: 'Manage your rentals on the go' },
    { id: 's2', num: '02', title: 'Fuel Plans', desc: 'Flexible refueling options' },
    { id: 's3', num: '03', title: 'Long Car Rental', desc: 'Monthly rental deals' },
    { id: 's4', num: '04', title: 'One-Way Rental', desc: 'Drop off at different location' },
    { id: 's5', num: '05', title: 'Groups', desc: 'Special group rates available' },
    { id: 's6', num: '06', title: 'Student Rental', desc: 'Affordable rates for students' },
  ];

  return (
    <div className="page-content">
      {/* Hero */}
      <section className="hero">
        <div className="container">
          <div className="text-box">
            <h3>Plan your trip now</h3>
            <h1>The <span>best</span> way to get a car</h1>
            <p>Discover premium vehicles for every occasion. Affordable prices, verified owners, and seamless booking experience across Sri Lanka.</p>
            <div className="btn-group">
              <Link to={dash.href} className="btn btn-primary btn-lg">{dash.text} <FiArrowRight /></Link>
              <Link to={dash.href2} className="btn btn-outline btn-lg">{dash.text2}</Link>
            </div>
          </div>
        </div>
      </section>

      {/* About Preview */}
      <section className="about-section">
        <div className="container">
          <div className="row" style={{ alignItems: 'center' }}>
            <div className="col" style={{ flex: 1 }}>
              <img src="https://images.unsplash.com/photo-1449965408869-ebd3fee7e9ee?w=600&h=400&fit=crop" alt="About YAMU" style={{ borderRadius: 'var(--radius)', width: '100%' }} />
            </div>
            <div className="col" style={{ flex: 1 }}>
              <h3 style={{ color: 'var(--accent)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: 2 }}>About Us</h3>
              <h2 style={{ fontSize: '2rem', fontWeight: 800, margin: '0.5rem 0 1rem' }}>More than 150+ special collection cars</h2>
              <p style={{ color: 'var(--text-light)', lineHeight: 1.8 }}>YAMU connects you with verified vehicle owners and professional drivers across Sri Lanka. Browse our curated collection and book with confidence.</p>
              <Link to="/cars" className="btn btn-primary" style={{ marginTop: '1.5rem' }}>See all cars</Link>
            </div>
          </div>
        </div>
      </section>

      {/* How it works */}
      <section className="process-section">
        <div className="container">
          <div className="section-header">
            <h3>Helps you to find your next car easily</h3>
            <h2>How it works</h2>
          </div>
          <div className="grid-3">
            {[
              { num: '1', title: 'Create Account', desc: 'Sign up as a customer, driver, or rental center in minutes.' },
              { num: '2', title: 'Contact Operator', desc: 'Browse vehicles or drivers and connect with operators directly.' },
              { num: '3', title: "Let's Drive", desc: 'Confirm your booking, make payment, and hit the road!' },
            ].map(p => (
              <div key={p.num} className="process-item">
                <h1 className="process-number">{p.num}</h1>
                <h3 className="process-title">{p.title}</h3>
                <p className="process-des">{p.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Vehicle Fleet */}
      <section style={{ padding: '5rem 0' }}>
        <div className="container">
          <div className="section-header">
            <h3>Helps you to find your next car easily</h3>
            <h2>Our rental fleet</h2>
          </div>
          <div className="grid-3">
            {vehicles.map(v => <VehicleCard key={v._id} vehicle={v} />)}
          </div>
          {vehicles.length === 0 && <p style={{ textAlign: 'center', color: 'var(--text-light)' }}>No vehicles available yet.</p>}
          <div style={{ textAlign: 'center', marginTop: '2rem' }}>
            <Link to="/cars" className="btn btn-primary">Show All Cars</Link>
          </div>
        </div>
      </section>

      {/* Services */}
      <section className="services-section">
        <div className="container">
          <div className="section-header">
            <h3>Our Services</h3>
            <h2>We have best services for rent cars</h2>
          </div>
          <div className="row">
            <div style={{ flex: '0 0 320px' }}>
              <div className="service-tabs">
                {services.map(s => (
                  <button key={s.id} className={`tab-btn ${activeTab === s.id ? 'active' : ''}`} onClick={() => setActiveTab(s.id)}>
                    <span>{s.num}</span>
                    <h4>{s.title}</h4>
                  </button>
                ))}
              </div>
            </div>
            <div style={{ flex: 1 }}>
              <div className="tab-content-area">
                {services.filter(s => s.id === activeTab).map(s => (
                  <div key={s.id}>
                    <span style={{ color: 'var(--accent)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: 2, fontSize: '0.85rem' }}>HELPS YOU TO FIND YOUR NEXT CAR EASILY</span>
                    <h2 style={{ fontSize: '1.75rem', fontWeight: 800, margin: '0.5rem 0 1rem' }}>{s.title}</h2>
                    <p style={{ color: 'var(--text-light)', lineHeight: 1.8 }}>{s.desc}. Our comprehensive rental services cover everything you need for a smooth journey. From airport pickups to long-term rentals, we've got you covered.</p>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Testimonials */}
      <section className="testimonial-section">
        <div className="container">
          <div className="section-header">
            <h3>Reviewed by People</h3>
            <h2>Why people love YAMU</h2>
          </div>
          <div className="grid-3">
            {['Amal Perera', 'Nishanth Kumar', 'Dilini Fernando'].map((name, i) => (
              <div key={i} className="card testimonial-card">
                <RiDoubleQuotesL className="quote-icon" />
                <p>Amazing service! Booked a car within minutes and the entire process was seamless. Highly recommend YAMU for anyone looking to rent a vehicle in Sri Lanka.</p>
                <hr style={{ border: 'none', borderTop: '1px solid var(--border)', margin: '1rem 0' }} />
                <img className="avatar" src={`https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=f0a500&color=0d1b2a&bold=true&size=56`} alt={name} />
                <span className="name">{name}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Banner */}
      <section className="cta-banner">
        <div className="container">
          <div className="row" style={{ alignItems: 'center', justifyContent: 'space-between' }}>
            <div>
              <h1>Save big with our cheap car rental!</h1>
              <h3>Top Airports. Local Suppliers. 24/7 Support.</h3>
            </div>
            <Link to="/cars" className="btn btn-secondary btn-lg">Book Ride</Link>
          </div>
        </div>
      </section>

      {/* FAQ */}
      <section className="faq-section">
        <div className="container">
          <div className="section-header">
            <h3>FAQ</h3>
            <h2>Frequently Asked Questions</h2>
          </div>
          <div style={{ maxWidth: 700, margin: '0 auto' }}>
            <div className="faqs">
              {[
                { q: 'What is about rental car deals?', a: 'We offer the best rental car deals in Sri Lanka with competitive prices and a wide range of vehicles to choose from.' },
                { q: 'In which areas do you operate?', a: 'We operate across all major cities including Colombo, Kandy, Galle, Negombo, and more.' },
                { q: 'How do I cancel a booking?', a: 'You can cancel your booking from your dashboard. Cancellation policies may vary depending on the rental center.' },
                { q: 'Are drivers verified?', a: 'Yes, all our drivers are verified with valid driving licenses and background checks for your safety.' },
                { q: 'What payment methods do you accept?', a: 'We accept cash payments and bank transfers. Online payment integration is coming soon.' },
              ].map((faq, i) => (
                <details key={i}>
                  <summary>{faq.q}</summary>
                  <p>{faq.a}</p>
                </details>
              ))}
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
