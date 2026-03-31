const generateBookingNumber = () => {
  return 'BOOK' + Math.floor(10000 + Math.random() * 90000);
};

const calcTotalDays = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const diffTime = end - start;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return Math.max(1, diffDays);
};

const hasDateOverlap = (existingStart, existingEnd, newStart, newEnd) => {
  return !(existingEnd < newStart || existingStart > newEnd);
};

module.exports = {
  generateBookingNumber,
  calcTotalDays,
  hasDateOverlap
};
