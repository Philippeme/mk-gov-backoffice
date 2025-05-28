<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $search = $request->query->get('search', '');
        $role = $request->query->get('role', 'all');
        $status = $request->query->get('status', 'all');

        $users = $this->getUsersList($page, $limit, $search, $role, $status);
        $totalUsers = $this->getTotalUsersCount($search, $role, $status);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalUsers / $limit),
                'total_items' => $totalUsers,
                'limit' => $limit
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status
            ],
            'roles' => $this->getRolesList(),
            'statuses' => $this->getStatusesList(),
            'user_statistics' => $this->getUserStatistics()
        ]);
    }

    #[Route('/admin/users/{id}', name: 'admin_users_show')]
    public function show(int $id): Response
    {
        $user = $this->getUserById($id);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
            'permissions' => $this->getUserPermissions($id),
            'activity_log' => $this->getUserActivityLog($id),
            'assigned_requests' => $this->getUserAssignedRequests($id)
        ]);
    }

    #[Route('/admin/users/new', name: 'admin_users_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $newUserId = $this->createUser($data);
            
            $this->addFlash('success', 'User created successfully');
            return $this->redirectToRoute('admin_users_show', ['id' => $newUserId]);
        }

        return $this->render('admin/users/new.html.twig', [
            'roles' => $this->getRolesList(),
            'public_entities' => $this->getPublicEntitiesList(),
            'departments' => $this->getDepartmentsList()
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_users_edit')]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->getUserById($id);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->updateUser($id, $data);
            
            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute('admin_users_show', ['id' => $id]);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'roles' => $this->getRolesList(),
            'public_entities' => $this->getPublicEntitiesList(),
            'departments' => $this->getDepartmentsList(),
            'user_permissions' => $this->getUserPermissions($id)
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $user = $this->getUserById($id);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->deleteUser($id);
        $this->addFlash('success', 'User deleted successfully');
        
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/users/{id}/toggle-status', name: 'admin_users_toggle_status', methods: ['POST'])]
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $newStatus = $this->toggleUserStatus($id);
            return new JsonResponse([
                'success' => true, 
                'message' => 'User status updated successfully',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function getUsersList(int $page, int $limit, string $search, string $role, string $status): array
    {
        return [
            [
                'id' => 1,
                'username' => 'admin.user',
                'email' => 'admin@mkgov.cm',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'role' => 'super_admin',
                'status' => 'active',
                'public_entity' => 'System Administration',
                'department' => 'IT Department',
                'last_login' => '2024-01-20 09:15:00',
                'created_at' => '2024-01-01 10:00:00',
                'requests_handled' => 0,
                'permissions_count' => 15
            ],
            [
                'id' => 2,
                'username' => 'registry.officer1',
                'email' => 'officer1@civilregistry.cm',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'role' => 'registry_officer',
                'status' => 'active',
                'public_entity' => 'Civil Registry Office',
                'department' => 'Birth Certificates',
                'last_login' => '2024-01-20 08:30:00',
                'created_at' => '2024-01-05 14:20:00',
                'requests_handled' => 145,
                'permissions_count' => 8
            ],
            [
                'id' => 3,
                'username' => 'passport.agent1',
                'email' => 'agent1@dgsn.cm',
                'first_name' => 'Paul',
                'last_name' => 'Martin',
                'role' => 'passport_agent',
                'status' => 'active',
                'public_entity' => 'DGSN',
                'department' => 'Passport Services',
                'last_login' => '2024-01-19 16:45:00',
                'created_at' => '2024-01-08 11:30:00',
                'requests_handled' => 89,
                'permissions_count' => 6
            ]
        ];
    }

    private function getTotalUsersCount(string $search, string $role, string $status): int
    {
        return 12;
    }

    private function getUserById(int $id): ?array
    {
        if ($id === 1) {
            return [
                'id' => 1,
                'username' => 'admin.user',
                'email' => 'admin@mkgov.cm',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'phone' => '+237123456789',
                'role' => 'super_admin',
                'status' => 'active',
                'public_entity' => 'System Administration',
                'department' => 'IT Department',
                'position' => 'System Administrator',
                'employee_id' => 'SYS001',
                'last_login' => '2024-01-20 09:15:00',
                'login_count' => 245,
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-15 14:30:00',
                'two_factor_enabled' => true,
                'password_last_changed' => '2024-01-10 12:00:00'
            ];
        }
        
        return null;
    }

    private function getUserPermissions(int $id): array
    {
        return [
            'dashboard_access' => true,
            'requests_view' => true,
            'requests_create' => true,
            'requests_edit' => true,
            'requests_delete' => true,
            'procedures_view' => true,
            'procedures_manage' => false,
            'users_view' => true,
            'users_manage' => false,
            'system_settings' => false,
            'reports_access' => true,
            'audit_logs' => false
        ];
    }

    private function getUserActivityLog(int $id): array
    {
        return [
            [
                'action' => 'Login',
                'description' => 'User logged into the system',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'timestamp' => '2024-01-20 09:15:00'
            ],
            [
                'action' => 'Request Approved',
                'description' => 'Approved birth certificate request REQ-2024-025',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'timestamp' => '2024-01-20 10:30:00'
            ],
            [
                'action' => 'Document Verified',
                'description' => 'Verified documents for request REQ-2024-026',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'timestamp' => '2024-01-20 11:45:00'
            ]
        ];
    }

    private function getUserAssignedRequests(int $id): array
    {
        return [
            [
                'id' => 15,
                'reference' => 'REQ-2024-015',
                'type' => 'Birth Certificate',
                'citizen_name' => 'Marie Claire',
                'status' => 'under_review',
                'assigned_at' => '2024-01-19 14:20:00',
                'due_date' => '2024-01-24 17:00:00'
            ],
            [
                'id' => 18,
                'reference' => 'REQ-2024-018',
                'type' => 'Marriage Certificate',
                'citizen_name' => 'Jean Baptiste',
                'status' => 'pending',
                'assigned_at' => '2024-01-20 08:30:00',
                'due_date' => '2024-01-25 17:00:00'
            ]
        ];
    }

    private function getUserStatistics(): array
    {
        return [
            'total_users' => 12,
            'active_users' => 10,
            'inactive_users' => 2,
            'admin_users' => 2,
            'regular_users' => 8,
            'guest_users' => 2,
            'online_now' => 5,
            'last_24h_logins' => 8
        ];
    }

    private function getRolesList(): array
    {
        return [
            'all' => 'All Roles',
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'registry_officer' => 'Registry Officer',
            'passport_agent' => 'Passport Agent',
            'transport_officer' => 'Transport Officer',
            'viewer' => 'Viewer'
        ];
    }

    private function getStatusesList(): array
    {
        return [
            'all' => 'All Statuses',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
            'pending' => 'Pending Activation'
        ];
    }

    private function getPublicEntitiesList(): array
    {
        return [
            1 => 'System Administration',
            2 => 'Civil Registry Office',
            3 => 'DGSN',
            4 => 'Ministry of Transport',
            5 => 'CFCE',
            6 => 'Prefecture'
        ];
    }

    private function getDepartmentsList(): array
    {
        return [
            'it' => 'IT Department',
            'birth_certificates' => 'Birth Certificates',
            'marriage_certificates' => 'Marriage Certificates',
            'passport_services' => 'Passport Services',
            'driving_licenses' => 'Driving Licenses',
            'business_registration' => 'Business Registration'
        ];
    }

    private function createUser(array $data): int
    {
        return rand(100, 999);
    }

    private function updateUser(int $id, array $data): void
    {
        // Logique de mise à jour
    }

    private function deleteUser(int $id): void
    {
        // Logique de suppression
    }

    private function toggleUserStatus(int $id): string
    {
        // Logique de basculement du statut
        return 'active';
    }
}