import { Link, NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useState } from 'react';
import { FiUser, FiLogOut, FiGrid, FiMenu, FiX } from 'react-icons/fi';
import { getDashboardLink } from '../utils/account';

export default function Navbar() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <nav className="navbar">
      <div className="container">
        <Link to="/" className="logo">YA<span>MU</span></Link>

        <div className={`nav-links ${menuOpen ? 'open' : ''}`}>
          <NavLink to="/" onClick={() => setMenuOpen(false)}>Home</NavLink>
          <NavLink to="/cars" onClick={() => setMenuOpen(false)}>Cars</NavLink>
          <NavLink to="/drivers" onClick={() => setMenuOpen(false)}>Drivers</NavLink>
          <NavLink to="/about" onClick={() => setMenuOpen(false)}>About</NavLink>
          <NavLink to="/contact" onClick={() => setMenuOpen(false)}>Contact</NavLink>
          <NavLink to="/blog" onClick={() => setMenuOpen(false)}>Blog</NavLink>
        </div>

        <div className="nav-auth">
          {user ? (
            <div className="nav-user">
              <img src={user.profilePic && user.profilePic !== 'avatar.png'
                ? `http://localhost:5000/uploads/${user.profilePic}`
                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.fullName) + '&background=f0a500&color=0d1b2a&bold=true'}
                alt={user.fullName}
              />
              <span>{user.fullName?.split(' ')[0]}</span>
              <div className="nav-user-dropdown">
                <Link to={getDashboardLink(user)}><FiGrid /> Dashboard</Link>
                <Link to="/account/my-profile"><FiUser /> My Profile</Link>
                <button onClick={handleLogout}><FiLogOut /> Logout</button>
              </div>
            </div>
          ) : (
            <>
              <Link to="/signin" className="btn btn-outline btn-sm">Sign In</Link>
              <Link to="/signup" className="btn btn-primary btn-sm">Sign Up</Link>
            </>
          )}
        </div>

        <button className="hamburger" onClick={() => setMenuOpen(!menuOpen)}>
          {menuOpen ? <FiX size={24} color="#fff" /> : <FiMenu size={24} color="#fff" />}
        </button>
      </div>
    </nav>
  );
}
