<aside class="w-64 bg-slate-900 text-white min-h-screen fixed top-0 left-0 flex flex-col shadow-xl z-50 transition-all duration-300">
    <div class="p-6 border-b border-slate-700 flex items-center justify-center">
        <h2 class="text-2xl font-bold tracking-wider text-blue-400">LMS Admin</h2>
    </div>
    
    <nav class="flex-1 overflow-y-auto py-6">
        <a href="/Library-management/dashboard/dashboard.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Dashboard</span>
        </a>
        
        <a href="/Library-management/books/books.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Books</span>
        </a>
        
        <a href="/Library-management/categories/categories.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Categories</span>
        </a>
        
        <a href="/Library-management/members/members.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Members</span>
        </a>

        <a href="/Library-management/issue/issueBook.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Issue Book</span>
        </a>

        <a href="/Library-management/return/returnBook.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Return Book</span>
        </a>

        <a href="/Library-management/librarians/librarians.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Librarians</span>
        </a>
        
        <a href="/Library-management/publishers/publishers.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Publishers</span>
        </a>

        <a href="/Library-management/reports/reports.php" 
           class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white transition-colors duration-200 border-l-4 border-transparent hover:border-blue-500">
           <span class="ml-2">Reports</span>
        </a>
    </nav>
    
    <div class="p-4 border-t border-slate-700">
        <a href="/Library-management/auth/logout.php" 
           class="block w-full text-center py-2 px-4 bg-red-600 hover:bg-red-700 text-white rounded transition duration-200">
           Logout
        </a>
    </div>
</aside>