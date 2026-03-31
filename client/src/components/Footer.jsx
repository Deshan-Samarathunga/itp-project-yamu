import { Link } from 'react-router-dom';

export default function Footer() {
  return (
    <footer className="footer">
      <div className="container">
        <div className="footer-grid">
          <div>
            <h3>YA<span style={{ color: 'var(--accent)' }}>MU</span></h3>
            <p>Your trusted vehicle rental platform. Book your perfect ride with the best deals in Sri Lanka. Quality cars, verified drivers, and seamless booking experience.</p>
          </div>
          <div>
            <h3>Quick Links</h3>
            <ul>
              <li><Link to="/cars">Browse Cars</Link></li>
              <li><Link to="/drivers">Find Drivers</Link></li>
              <li><Link to="/about">About Us</Link></li>
              <li><Link to="/blog">Blog</Link></li>
            </ul>
          </div>
          <div>
            <h3>Support</h3>
            <ul>
              <li><Link to="/contact">Contact Us</Link></li>
              <li><Link to="/terms">Terms & Conditions</Link></li>
              <li><Link to="/signin">Login</Link></li>
              <li><Link to="/signup">Register</Link></li>
            </ul>
          </div>
          <div>
            <h3>Contact</h3>
            <ul>
              <li>📍 Colombo, Sri Lanka</li>
              <li>📞 +94 77 123 4567</li>
              <li>✉️ info@yamu.lk</li>
            </ul>
          </div>
        </div>
        <div className="footer-bottom">
          <p>&copy; {new Date().getFullYear()} YAMU. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
}
