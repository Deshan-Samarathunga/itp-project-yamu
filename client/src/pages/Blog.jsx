export default function Blog() {
  const posts = [
    { id: 1, title: 'Impact Of Extrinsic Motivation On Car Rentals', date: '22 Oct 2024', author: 'YAMU Team', excerpt: 'Explore how external factors drive the car rental industry forward and what it means for customers seeking the best deals.', img: 'https://images.unsplash.com/photo-1485291571150-772bcfc10da5?w=400&h=250&fit=crop' },
    { id: 2, title: 'Top 5 Road Trips From Colombo', date: '15 Nov 2024', author: 'Travel Desk', excerpt: 'Discover the most scenic routes from Colombo. Perfect weekend getaways with your rented vehicle from YAMU.', img: 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=400&h=250&fit=crop' },
    { id: 3, title: 'Electric Vehicles: The Future of Rentals', date: '03 Dec 2024', author: 'YAMU Team', excerpt: 'How electric vehicles are changing the car rental landscape in Sri Lanka and what to expect in the coming years.', img: 'https://images.unsplash.com/photo-1593941707882-a5bba14938c7?w=400&h=250&fit=crop' },
  ];

  return (
    <div className="page-content">
      <div className="page-header">
        <h1>Blog</h1>
        <p>Latest news and insights</p>
      </div>
      <section className="blog-section">
        <div className="container">
          <div className="grid-3">
            {posts.map(p => (
              <div key={p.id} className="card blog-card">
                <div className="card-img">
                  <img src={p.img} alt={p.title} />
                </div>
                <div className="card-body">
                  <div className="blog-meta">
                    <span>{p.date}</span>
                    <span>{p.author}</span>
                  </div>
                  <h3>{p.title}</h3>
                  <p>{p.excerpt}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
