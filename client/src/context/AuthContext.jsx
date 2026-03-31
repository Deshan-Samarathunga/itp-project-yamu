import { createContext, useContext, useState, useEffect } from 'react';
import API from '../api/axios';

const AuthContext = createContext(null);

export const useAuth = () => useContext(AuthContext);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('yamu_token'));
  const [loading, setLoading] = useState(true);

  const persistSession = (jwt, userData) => {
    if (jwt) {
      setToken(jwt);
      localStorage.setItem('yamu_token', jwt);
    }

    setUser(userData);
    localStorage.setItem('yamu_user', JSON.stringify(userData));
  };

  const refreshUser = async () => {
    const res = await API.get('/auth/me');
    setUser(res.data);
    localStorage.setItem('yamu_user', JSON.stringify(res.data));
    return res.data;
  };

  useEffect(() => {
    if (token) {
      refreshUser()
        .catch(() => {
          logout();
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
    }
  }, [token]);

  const login = async (email, password) => {
    const res = await API.post('/auth/login', { email, password });
    const { token: jwt, ...userData } = res.data;
    persistSession(jwt, userData);
    return userData;
  };

  const register = async (data) => {
    const res = await API.post('/auth/register', data);
    const { token: jwt, ...userData } = res.data;
    persistSession(jwt, userData);
    return userData;
  };

  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('yamu_token');
    localStorage.removeItem('yamu_user');
  };

  const switchRole = async (role) => {
    const res = await API.put('/auth/switch-role', { role });
    const { token: jwt, ...userData } = res.data;
    persistSession(jwt, userData);
    return userData;
  };

  const value = { user, token, loading, login, register, logout, switchRole, refreshUser, setUser };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
