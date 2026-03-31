const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');
require('dotenv').config();

const User = require('../models/User');
const Vehicle = require('../models/Vehicle');
const Brand = require('../models/Brand');
const CustomerProfile = require('../models/CustomerProfile');
const DriverProfile = require('../models/DriverProfile');
const StaffProfile = require('../models/StaffProfile');
const AdminProfile = require('../models/AdminProfile');
const RoleApplication = require('../models/RoleApplication');

const seedDB = async () => {
  try {
    await mongoose.connect(process.env.MONGODB_URI);
    console.log('MongoDB Connected for seeding...');

    // Clear existing data
    await User.deleteMany({});
    await Vehicle.deleteMany({});
    await Brand.deleteMany({});
    await CustomerProfile.deleteMany({});
    await DriverProfile.deleteMany({});
    await StaffProfile.deleteMany({});
    await AdminProfile.deleteMany({});
    await RoleApplication.deleteMany({});

    // Seed Brands
    const brands = await Brand.insertMany([
      { brandName: 'Audi', brandLogo: 'audi.png' },
      { brandName: 'BMW', brandLogo: 'BMW.png' },
      { brandName: 'Ford', brandLogo: 'ford.png' },
      { brandName: 'KIA', brandLogo: 'kia.png' },
      { brandName: 'Mitsubishi', brandLogo: 'Mitsubishi.png' },
      { brandName: 'Nissan', brandLogo: 'nissan.png' },
      { brandName: 'Tesla', brandLogo: 'tesla.png' },
      { brandName: 'Toyota', brandLogo: 'Toyota.png' },
      { brandName: 'Volkswagen', brandLogo: 'Volkswagen.png' },
      { brandName: 'Benz', brandLogo: 'mercedes-logo.png' },
      { brandName: 'Peugeot', brandLogo: 'peugeot.png' },
      { brandName: 'Suzuki', brandLogo: 'suzuki.png' }
    ]);
    console.log('Brands seeded');

    // Seed Users
    const adminUser = await User.create({
      username: 'admin',
      password: '12345',
      role: 'admin',
      fullName: 'Admin',
      email: 'admin@email.com',
      address: '9091 Hillcrest Rd',
      city: 'Colombo',
      phone: '0769643114',
      accountStatus: 'active',
      verificationStatus: 'verified',
      roles: [{ roleKey: 'admin', roleStatus: 'active', verificationStatus: 'verified', isPrimary: true }],
      adminProfile: { systemPermissions: 'all' }
    });

    const staffUser = await User.create({
      username: 'sample.staff@yamu.com',
      password: '12345',
      role: 'staff',
      fullName: 'Yamu Rental Center',
      email: 'sample.staff@yamu.com',
      address: '155 Galle Road, Colombo 03',
      city: 'Colombo',
      phone: '0775550100',
      bio: 'Sample seeded rental center account for vehicle listings.',
      accountStatus: 'active',
      verificationStatus: 'verified',
      roles: [{ roleKey: 'staff', roleStatus: 'active', verificationStatus: 'verified', isPrimary: true }],
      staffProfile: {
        storeName: 'Yamu Rental Center',
        storeOwner: 'Yamu Rental Center',
        businessRegistrationNumber: 'YAMU-STF-001',
        storeAddress: '155 Galle Road, Colombo 03',
        storeContactNumber: '0775550100',
        storeEmail: 'sample.staff@yamu.com',
        verificationStatus: 'verified',
        verifiedAt: new Date()
      }
    });

    const customerUser = await User.create({
      username: 'customer@test.com',
      password: '12345',
      role: 'customer',
      fullName: 'Eran Madhuka',
      email: 'customer@test.com',
      address: 'No:94, willorawatta, moratuwa.',
      city: 'Moratuwa',
      phone: '0785862007',
      accountStatus: 'active',
      verificationStatus: 'verified',
      roles: [{ roleKey: 'customer', roleStatus: 'active', verificationStatus: 'verified', isPrimary: true }]
    });

    const pendingDriverApplicant = await User.create({
      username: 'driver.apply@yamu.com',
      password: '12345',
      role: 'customer',
      fullName: 'Pending Driver Applicant',
      email: 'driver.apply@yamu.com',
      address: '12 Lake Drive, Galle',
      city: 'Galle',
      phone: '0775550123',
      bio: 'Customer account applying for driver onboarding.',
      accountStatus: 'active',
      verificationStatus: 'verified',
      roles: [
        { roleKey: 'customer', roleStatus: 'active', verificationStatus: 'verified', isPrimary: true },
        { roleKey: 'driver', roleStatus: 'pending', verificationStatus: 'pending', isPrimary: false }
      ],
      driverProfile: {
        drivingLicenseNumber: 'B1234567',
        nicId: '992223334V',
        serviceArea: 'Southern Province',
        providerDetails: 'Comfortable with airport pickups and city hires.',
        verificationStatus: 'pending'
      }
    });

    console.log('Users seeded');

    await CustomerProfile.insertMany([
      {
        user: customerUser._id,
        preferredContactMethod: 'phone',
        emergencyContactName: 'Nadeesha Perera',
        emergencyContactPhone: '0711234567',
        notes: 'Prefers weekend bookings.'
      },
      {
        user: pendingDriverApplicant._id,
        preferredContactMethod: 'email',
        emergencyContactName: 'Saman Silva',
        emergencyContactPhone: '0770001122'
      }
    ]);

    await StaffProfile.create({
      user: staffUser._id,
      storeName: 'Yamu Rental Center',
      storeOwner: 'Yamu Rental Center',
      businessRegistrationNumber: 'YAMU-STF-001',
      storeAddress: '155 Galle Road, Colombo 03',
      storeContactNumber: '0775550100',
      storeEmail: 'sample.staff@yamu.com',
      onboardingCompleted: true
    });

    await AdminProfile.create({
      user: adminUser._id,
      systemPermissions: 'all',
      adminNotes: 'Seeded system administrator'
    });

    await DriverProfile.create({
      user: pendingDriverApplicant._id,
      drivingLicenseNumber: 'B1234567',
      licenseExpiryDate: new Date('2028-12-31'),
      nicId: '992223334V',
      serviceArea: 'Southern Province',
      providerDetails: 'Comfortable with airport pickups and city hires.',
      onboardingCompleted: true
    });

    await RoleApplication.create({
      applicant: pendingDriverApplicant._id,
      roleKey: 'driver',
      motivation: 'I want to offer driver services for local and airport trips.',
      status: 'pending'
    });

    // Seed Vehicles
    await Vehicle.insertMany([
      {
        owner: staffUser._id,
        vehicleTitle: 'Maruti Suzuki Wagon R',
        vehicleBrand: 'Suzuki',
        vehicleDesc: 'Maruti Wagon R with BS6 engine, fuel economy of 21.79 kmpl. 1.0-litre powertrain with 68PS of power and 90Nm of torque.',
        price: 4500,
        transmission: 'Automatic',
        fuelType: 'Petrol',
        year: 2019,
        engineCapacity: '1000',
        capacity: 5,
        location: 'Colombo',
        registrationNumber: 'CAR-1001',
        features: { airConditioner: true },
        listingStatus: 'approved',
        availabilityStatus: 'available',
        approvedBy: adminUser._id,
        approvedAt: new Date()
      },
      {
        owner: staffUser._id,
        vehicleTitle: 'Mercedes AMG',
        vehicleBrand: 'Benz',
        vehicleDesc: 'Premium luxury vehicle with 3900cc engine, automatic transmission. Perfect for long drives and special occasions.',
        price: 8500,
        transmission: 'Automatic',
        fuelType: 'Petrol',
        year: 2015,
        engineCapacity: '3900',
        capacity: 5,
        location: 'Colombo',
        registrationNumber: 'CAR-1002',
        features: { airConditioner: true },
        listingStatus: 'approved',
        availabilityStatus: 'available',
        approvedBy: adminUser._id,
        approvedAt: new Date()
      },
      {
        owner: staffUser._id,
        vehicleTitle: 'Audi Q8',
        vehicleBrand: 'Audi',
        vehicleDesc: 'Premium SUV with 3000cc engine. Comfortable for family trips with spacious interiors.',
        price: 6500,
        transmission: 'Automatic',
        fuelType: 'Petrol',
        year: 2017,
        engineCapacity: '3000',
        capacity: 5,
        location: 'Negombo',
        registrationNumber: 'CAR-1003',
        features: { airConditioner: true },
        listingStatus: 'approved',
        availabilityStatus: 'available',
        approvedBy: adminUser._id,
        approvedAt: new Date()
      },
      {
        owner: staffUser._id,
        vehicleTitle: 'Toyota Fortuner',
        vehicleBrand: 'Toyota',
        vehicleDesc: 'Premium seven-seater SUV with LED projector headlamps, automatic climate control, push-button start, cruise control, and 7 airbags.',
        price: 9500,
        transmission: 'Automatic',
        fuelType: 'Diesel',
        year: 2020,
        engineCapacity: '3500',
        capacity: 8,
        location: 'Colombo',
        registrationNumber: 'CAR-1004',
        listingStatus: 'approved',
        availabilityStatus: 'available',
        approvedBy: adminUser._id,
        approvedAt: new Date()
      },
      {
        owner: staffUser._id,
        vehicleTitle: 'Nissan Kicks',
        vehicleBrand: 'Nissan',
        vehicleDesc: 'Compact SUV with turbo petrol engine. Available in four variants: XL, XV, XV Premium, and XV Premium(O).',
        price: 6500,
        transmission: 'Automatic',
        fuelType: 'Diesel',
        year: 2020,
        engineCapacity: '2500',
        capacity: 8,
        location: 'Kandy',
        registrationNumber: 'CAR-1005',
        features: { airConditioner: true },
        listingStatus: 'approved',
        availabilityStatus: 'available',
        approvedBy: adminUser._id,
        approvedAt: new Date()
      }
    ]);
    console.log('Vehicles seeded');

    console.log('\n=== Seed Complete ===');
    console.log('Admin:    admin@email.com / 12345');
    console.log('Staff:    sample.staff@yamu.com / 12345');
    console.log('Customer: customer@test.com / 12345');
    console.log('Pending Driver Applicant: driver.apply@yamu.com / 12345');
    console.log('====================\n');

    process.exit(0);
  } catch (error) {
    console.error('Seed error:', error);
    process.exit(1);
  }
};

seedDB();
