import React, { useState, useEffect, useRef } from 'react';
import { BellIcon, BellAlertIcon } from '@heroicons/react/24/outline';
import axios from 'axios';
import { Link } from '@inertiajs/react';

export default function NotificationDropdown({ user }) {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const dropdownRef = useRef(null);

  // Load notifications when dropdown is opened
  useEffect(() => {
    // Add event listener to close dropdown when clicking outside
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    // Attach the event listener
    document.addEventListener('mousedown', handleClickOutside);
    
    // Clean up
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  // Fetch notifications count on initial load
  useEffect(() => {
    fetchUnreadCount();
    
    // Set up polling for new notifications every 30 seconds
    const intervalId = setInterval(fetchUnreadCount, 30000);
    
    return () => clearInterval(intervalId);
  }, []);

  const fetchUnreadCount = async () => {
    try {
      const response = await axios.get('/api/notifications/unread-count');
      setUnreadCount(response.data.count);
    } catch (error) {
      console.error('Error fetching notification count:', error);
    }
  };

  const fetchNotifications = async () => {
    if (loading) return;
    
    setLoading(true);
    try {
      const response = await axios.get('/api/notifications');
      setNotifications(response.data);
      
      // Update unread count
      const unreadNotifications = response.data.filter(n => !n.read);
      setUnreadCount(unreadNotifications.length);
    } catch (error) {
      console.error('Error fetching notifications:', error);
    } finally {
      setLoading(false);
    }
  };

  const markAsRead = async (id) => {
    try {
      await axios.post(`/api/notifications/${id}/mark-as-read`);
      
      // Update the notification in the local state
      setNotifications(prev => 
        prev.map(notification => 
          notification.id === id 
            ? { ...notification, read: true } 
            : notification
        )
      );
      
      // Update unread count
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  };

  const markAllAsRead = async () => {
    try {
      await axios.post('/api/notifications/mark-all-read');
      
      // Update all notifications in local state
      setNotifications(prev => 
        prev.map(notification => ({ ...notification, read: true }))
      );
      
      // Reset unread count
      setUnreadCount(0);
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };
  
  const toggleDropdown = () => {
    const nextState = !isOpen;
    setIsOpen(nextState);
    
    if (nextState) {
      fetchNotifications();
    }
  };

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={toggleDropdown}
        className="p-2 text-gray-500 hover:text-gray-700 focus:outline-none relative"
        aria-expanded={isOpen}
      >
        {unreadCount > 0 ? (
          <>
            <BellAlertIcon className="h-6 w-6 text-indigo-500" />
            <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
              {unreadCount}
            </span>
          </>
        ) : (
          <BellIcon className="h-6 w-6" />
        )}
      </button>

      {isOpen && (
        <div className="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
          <div className="py-1">
            <div className="px-4 py-2 border-b flex justify-between items-center">
              <h3 className="text-sm font-medium text-gray-900">Notificaciones</h3>
              {unreadCount > 0 && (
                <button
                  onClick={markAllAsRead}
                  className="text-xs text-indigo-600 hover:text-indigo-800"
                >
                  Marcar todas como leídas
                </button>
              )}
            </div>
            
            <div className="max-h-96 overflow-y-auto">
              {loading && notifications.length === 0 ? (
                <div className="px-4 py-8 text-center text-sm text-gray-500">
                  <div className="inline-block animate-spin rounded-full h-4 w-4 border-2 border-gray-200 border-t-indigo-600 mr-2"></div>
                  Cargando notificaciones...
                </div>
              ) : notifications.length === 0 ? (
                <div className="px-4 py-8 text-center text-sm text-gray-500">
                  No tienes notificaciones
                </div>
              ) : (
                notifications.map((notification) => {
                  const commenter = notification.commenter;
                  const decision = notification.decision;
                  return (
                    <div
                      key={notification.id}
                      className={`px-4 py-3 border-b hover:bg-gray-50 ${
                        !notification.read ? 'bg-blue-50' : ''
                      }`}
                      onClick={() => markAsRead(notification.id)}
                    >
                      <Link
                        href={`/decisions/${notification.decision_id}`}
                        className="block"
                      >
                        <div className="flex items-start space-x-3">
                          {commenter?.avatar ? (
                            <img
                              src={commenter.avatar}
                              alt={commenter.name}
                              className="h-8 w-8 rounded-full object-cover"
                            />
                          ) : (
                            <div className="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500">
                              {commenter?.name?.charAt(0) || '?'}
                            </div>
                          )}
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-900 truncate">
                              {commenter?.name} comentó en tu decisión
                            </p>
                            <p className="mt-1 text-xs text-gray-700 truncate">
                              <span className="font-medium">{decision?.title}</span>
                            </p>
                            <p className="mt-1 text-xs text-gray-500 line-clamp-2">
                              {notification.comment_preview}
                            </p>
                            <p className="mt-1 text-xs text-gray-400">
                              {formatDate(notification.created_at)}
                            </p>
                          </div>
                        </div>
                      </Link>
                    </div>
                  );
                })
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
