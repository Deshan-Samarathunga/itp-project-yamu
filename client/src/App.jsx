import { Navigate, Routes, Route } from 'react-router-dom';
import { useAuth } from './context/AuthContext';

// Layout
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import ProtectedRoute from './components/ProtectedRoute';

// Public Pages
import Home from './pages/Home';
import About from './pages/About';
import Contact from './pages/Contact';
import Blog from './pages/Blog';
import CarListing from './pages/CarListing';
import VehicleDetails from './pages/VehicleDetails';
import Drivers from './pages/Drivers';

// Auth Pages
import SignIn from './pages/SignIn';
import SignUp from './pages/SignUp';

// Customer Pages
import MyBookings from './pages/customer/MyBookings';
import MyReviews from './pages/customer/MyReviews';
import MyDisputes from './pages/customer/MyDisputes';
import PaymentHistory from './pages/customer/PaymentHistory';

// Account Pages
import AccountMyProfile from './pages/account/MyProfile';
import EditProfile from './pages/account/EditProfile';
import UpdatePassword from './pages/account/UpdatePassword';
import RoleProfilePage from './pages/account/RoleProfilePage';
import ChooseRole from './pages/account/ChooseRole';
import RoleSwitch from './pages/account/RoleSwitch';
import RoleApplication from './pages/account/RoleApplication';

// Driver Pages
import DriverDashboard from './pages/driver/Dashboard';
import DriverBookings from './pages/driver/Bookings';
import DriverAds from './pages/driver/Ads';
import DriverAdForm from './pages/driver/AdForm';
import DriverEarnings from './pages/driver/Earnings';
import DriverReviews from './pages/driver/Reviews';
import DriverDisputes from './pages/driver/Disputes';

// Staff Pages
import StaffDashboard from './pages/staff/Dashboard';
import StaffVehicles from './pages/staff/Vehicles';
import StaffVehicleForm from './pages/staff/VehicleForm';
import StaffBookings from './pages/staff/Bookings';

// Admin Pages
import AdminDashboard from './pages/admin/Dashboard';
import AdminUsers from './pages/admin/Users';
import AdminUserDetails from './pages/admin/UserDetails';
import AdminProviderApplications from './pages/admin/ProviderApplications';
import AdminVehicles from './pages/admin/Vehicles';
import AdminBookings from './pages/admin/Bookings';
import AdminDisputes from './pages/admin/Disputes';
import AdminReviews from './pages/admin/Reviews';
import AdminPayments from './pages/admin/Payments';
import AdminPromotions from './pages/admin/Promotions';
import AdminPromotionForm from './pages/admin/PromotionForm';
import AdminBrands from './pages/admin/Brands';

function App() {
  const { loading } = useAuth();

  if (loading) {
    return (
      <div className="spinner-container">
        <div className="spinner"></div>
      </div>
    );
  }

  return (
    <>
      <Navbar />
      <Routes>
        {/* Public */}
        <Route path="/" element={<><Home /><Footer /></>} />
        <Route path="/about" element={<><div className="page-content"><About /></div><Footer /></>} />
        <Route path="/contact" element={<><Contact /><Footer /></>} />
        <Route path="/blog" element={<><Blog /><Footer /></>} />
        <Route path="/cars" element={<><CarListing /><Footer /></>} />
        <Route path="/cars/:id" element={<><VehicleDetails /><Footer /></>} />
        <Route path="/drivers" element={<><Drivers /><Footer /></>} />

        {/* Auth */}
        <Route path="/signin" element={<SignIn />} />
        <Route path="/signup" element={<SignUp />} />

        {/* Account */}
        <Route path="/profile" element={<Navigate to="/account/my-profile" replace />} />
        <Route path="/customer/profile" element={<Navigate to="/account/my-profile" replace />} />
        <Route path="/account/my-profile" element={<ProtectedRoute><AccountMyProfile /></ProtectedRoute>} />
        <Route path="/account/edit-profile" element={<ProtectedRoute><EditProfile /></ProtectedRoute>} />
        <Route path="/account/update-password" element={<ProtectedRoute><UpdatePassword /></ProtectedRoute>} />
        <Route path="/account/customer-profile" element={<ProtectedRoute><RoleProfilePage roleKey="customer" /></ProtectedRoute>} />
        <Route path="/account/driver-profile" element={<ProtectedRoute><RoleProfilePage roleKey="driver" /></ProtectedRoute>} />
        <Route path="/account/staff-profile" element={<ProtectedRoute><RoleProfilePage roleKey="staff" /></ProtectedRoute>} />
        <Route path="/account/admin-profile" element={<ProtectedRoute><RoleProfilePage roleKey="admin" /></ProtectedRoute>} />
        <Route path="/account/choose-role" element={<ProtectedRoute><ChooseRole /></ProtectedRoute>} />
        <Route path="/account/role-switch" element={<ProtectedRoute><RoleSwitch /></ProtectedRoute>} />
        <Route path="/account/role-application" element={<ProtectedRoute><RoleApplication /></ProtectedRoute>} />

        {/* Customer */}
        <Route path="/customer/bookings" element={<ProtectedRoute roles={['customer', 'admin']}><MyBookings /></ProtectedRoute>} />
        <Route path="/customer/reviews" element={<ProtectedRoute><MyReviews /></ProtectedRoute>} />
        <Route path="/customer/disputes" element={<ProtectedRoute><MyDisputes /></ProtectedRoute>} />
        <Route path="/customer/payments" element={<ProtectedRoute><PaymentHistory /></ProtectedRoute>} />

        {/* Driver */}
        <Route path="/driver/dashboard" element={<ProtectedRoute roles={['driver']}><DriverDashboard /></ProtectedRoute>} />
        <Route path="/driver/bookings" element={<ProtectedRoute roles={['driver']}><DriverBookings /></ProtectedRoute>} />
        <Route path="/driver/ads" element={<ProtectedRoute roles={['driver']}><DriverAds /></ProtectedRoute>} />
        <Route path="/driver/ads/new" element={<ProtectedRoute roles={['driver']}><DriverAdForm /></ProtectedRoute>} />
        <Route path="/driver/ads/edit/:id" element={<ProtectedRoute roles={['driver']}><DriverAdForm /></ProtectedRoute>} />
        <Route path="/driver/earnings" element={<ProtectedRoute roles={['driver']}><DriverEarnings /></ProtectedRoute>} />
        <Route path="/driver/reviews" element={<ProtectedRoute roles={['driver']}><DriverReviews /></ProtectedRoute>} />
        <Route path="/driver/disputes" element={<ProtectedRoute roles={['driver']}><DriverDisputes /></ProtectedRoute>} />

        {/* Staff */}
        <Route path="/staff/dashboard" element={<ProtectedRoute roles={['staff']}><StaffDashboard /></ProtectedRoute>} />
        <Route path="/staff/vehicles" element={<ProtectedRoute roles={['staff']}><StaffVehicles /></ProtectedRoute>} />
        <Route path="/staff/vehicles/new" element={<ProtectedRoute roles={['staff']}><StaffVehicleForm /></ProtectedRoute>} />
        <Route path="/staff/vehicles/edit/:id" element={<ProtectedRoute roles={['staff']}><StaffVehicleForm /></ProtectedRoute>} />
        <Route path="/staff/bookings" element={<ProtectedRoute roles={['staff']}><StaffBookings /></ProtectedRoute>} />

        {/* Admin */}
        <Route path="/admin/dashboard" element={<ProtectedRoute roles={['admin']}><AdminDashboard /></ProtectedRoute>} />
        <Route path="/admin/users" element={<ProtectedRoute roles={['admin']}><AdminUsers /></ProtectedRoute>} />
        <Route path="/admin/users/:id" element={<ProtectedRoute roles={['admin']}><AdminUserDetails /></ProtectedRoute>} />
        <Route path="/admin/provider-applications" element={<ProtectedRoute roles={['admin']}><AdminProviderApplications /></ProtectedRoute>} />
        <Route path="/admin/vehicles" element={<ProtectedRoute roles={['admin']}><AdminVehicles /></ProtectedRoute>} />
        <Route path="/admin/bookings" element={<ProtectedRoute roles={['admin']}><AdminBookings /></ProtectedRoute>} />
        <Route path="/admin/disputes" element={<ProtectedRoute roles={['admin']}><AdminDisputes /></ProtectedRoute>} />
        <Route path="/admin/reviews" element={<ProtectedRoute roles={['admin']}><AdminReviews /></ProtectedRoute>} />
        <Route path="/admin/payments" element={<ProtectedRoute roles={['admin']}><AdminPayments /></ProtectedRoute>} />
        <Route path="/admin/promotions" element={<ProtectedRoute roles={['admin']}><AdminPromotions /></ProtectedRoute>} />
        <Route path="/admin/promotions/new" element={<ProtectedRoute roles={['admin']}><AdminPromotionForm /></ProtectedRoute>} />
        <Route path="/admin/promotions/edit/:id" element={<ProtectedRoute roles={['admin']}><AdminPromotionForm /></ProtectedRoute>} />
        <Route path="/admin/brands" element={<ProtectedRoute roles={['admin']}><AdminBrands /></ProtectedRoute>} />

        {/* Catch-all */}
        <Route path="*" element={
          <div className="page-content" style={{ minHeight: '80vh', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <div className="empty-state">
              <h1 style={{ fontSize: '4rem', fontWeight: 800, color: 'var(--accent)' }}>404</h1>
              <h3>Page Not Found</h3>
              <p>The page you're looking for doesn't exist.</p>
            </div>
          </div>
        } />
      </Routes>
    </>
  );
}

export default App;
