<x-app-layout>
    <link rel="stylesheet" href="{{ URL::asset('css/cust.css') }}">
    <div class="container" style="min-width: 1000px; max-width: 100%; margin: 0 auto;">
        <h1>View Users</h1>
        {{-- Display how many users are shown on this page --}}
        <p>Displaying {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} total users</p>

        <div class="info">
            <div class="filter-search">
                <form action="{{ route('sAdminViewUsers') }}" method="GET" 
                      style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap;"
                >
                    <label for="filter">Filter by:</label>
                    <select name="filter" id="filter">
                        <option value="">Select Column</option>
                        <option value="email"  {{ request('filter') == 'email'  ? 'selected' : '' }}>Email</option>
                        <option value="role"   {{ request('filter') == 'role'   ? 'selected' : '' }}>Role (ID or Name)</option>
                        <option value="country"{{ request('filter') == 'country'? 'selected' : '' }}>Country</option>
                        <option value="state"  {{ request('filter') == 'state'  ? 'selected' : '' }}>State</option>
                        <option value="city"   {{ request('filter') == 'city'   ? 'selected' : '' }}>City</option>
                    </select>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." />

                    <label for="limit">Show:</label>
                    <select name="limit" id="limit">
                        @foreach([5, 10, 20, 50] as $option)
                            <option value="{{ $option }}" {{ request('limit') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" style="max-width: 100px;">Search</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Country</th>
                        <th>State</th>
                        <th>City</th>
                        <th>Zip Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role_name }}</td>
                            <td>{{ $user->country }}</td>
                            <td>{{ $user->state }}</td>
                            <td>{{ $user->city }}</td>
                            <td>{{ $user->zip_code }}</td>
                            <td class="action-buttons" style="display: flex; justify-content: center; align-items: left; gap: 10px;">
                                <!-- Edit Button -->
                                <button style="background-color: blue; color: white;" onclick="openEditModal({{ json_encode($user) }})">
                                    Edit
                                </button>

                                <!-- Delete Button -->
                                <form action="{{ route('deleteUser', ['user' => $user->id]) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background-color: red; color: white;" onclick="return confirm('Are you sure?');">
                                        Delete
                                    </button>
                                </form>
                            </td>
                    @empty
                        <tr>
                            <td colspan="7">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{-- Updated pagination message to reflect actual counts --}}
            <p class="pagination-message">
                Showing 
                <span class="font-medium">{{ $users->firstItem() }}</span> 
                to 
                <span class="font-medium">{{ $users->lastItem() }}</span> 
                of 
                <span class="font-medium">{{ $users->total() }}</span> 
                results
            </p>

            <div class="pagination-navigation" style="justify-content: center;">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px; border: 1px solid #ccc; z-index: 1000; width: 300px; max-width: 90%;">     
        <h3>Edit User</h3>
            <form id="editForm">
                @csrf
                <input type="hidden" id="userId" name="userId">
                
                <!-- Editable fields -->
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>

                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="1">Super Admin</option>
                    <option value="2">Admin</option>
                    <option value="3">General User</option>
                </select><br><br>

                <label for="country">Country:</label>
                <input type="text" id="country" name="country"><br><br>

                <label for="state">State:</label>
                <input type="text" id="state" name="state"><br><br>

                <label for="city">City:</label>
                <input type="text" id="city" name="city"><br><br>

                <label for="zip_code">Zip Code:</label>
                <input type="text" id="zip_code" name="zip_code"><br><br>

                <button type="submit" style="background-color: green; color: white;">Save Changes</button>
                <button type="button" onclick="closeEditModal()" style="background-color: gray; color: white;">Cancel</button>
            </form>
    </div>
    <div id="overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999;" onclick="closeEditModal()"></div>
    <script>

        function openEditModal(user) {
            // Populate modal fields with user data
            document.getElementById('userId').value = user.id;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('country').value = user.country;
            document.getElementById('state').value = user.state;
            document.getElementById('city').value = user.city;
            document.getElementById('zip_code').value = user.zip_code;

            // Show modal
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeEditModal() {
            // Hide modal
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission

            const userId = document.getElementById('userId').value;
            const formData = new FormData(this);

            fetch(`/users/${userId}/edit`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully!');
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert(`Failed to update user: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error updating user:', error);
                alert('An error occurred while updating the user.');
            });
        });
    </script>
</x-app-layout>
