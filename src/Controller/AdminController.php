<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\ProductStatus;
use App\Form\CategoryType;
use App\Form\OrderStatusType;
use App\Form\ProductType;
use App\Form\UserEditType;
use App\Form\UserType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\ImageUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin', name: 'app_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'dashboard')]
    public function dashboard(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        OrderRepository $orderRepository,
        UserRepository $userRepository
    ): Response {
        $totalUsers = $userRepository->count([]);
        $totalProducts = $productRepository->count([]);
        $totalOrders = $orderRepository->countTotal();
        $totalCategories = $categoryRepository->count([]);

        $categoriesWithCounts = $categoryRepository->findAllHierarchicalWithCounts();

        $latestOrders = $orderRepository->findLatestOrders(5);

        $productStatusCounts = $productRepository->countByStatus();
        $productStatusRatio = [];
        if ($totalProducts > 0) {
            foreach (ProductStatus::cases() as $status) {
                $count = $productStatusCounts[$status->value] ?? 0;
                $productStatusRatio[] = [
                    'status' => $status,
                    'count' => $count,
                    'percentage' => round(($count / $totalProducts) * 100, 1),
                ];
            }
        }

        $monthlySales = $orderRepository->getMonthlySalesForDeliveredOrders(12);

        return $this->render('admin/dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalCategories' => $totalCategories,
            'categoriesWithCounts' => $categoriesWithCounts,
            'latestOrders' => $latestOrders,
            'productStatusRatio' => $productStatusRatio,
            'monthlySales' => $monthlySales,
        ]);
    }

    #[Route('/products', name: 'products')]
    public function products(
        ProductRepository $productRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $queryBuilder = $productRepository->findAllOrderedQueryBuilder();

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        $productsData = [];
        foreach ($pagination as $product) {
            $productsData[] = [
                'product' => $product,
                'imagesCount' => count($product->getImages()),
                'orderItemsCount' => $productRepository->countOrderItems($product),
            ];
        }

        return $this->render('admin/products/index.html.twig', [
            'productsData' => $productsData,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/products/new', name: 'product_new')]
    public function productNew(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        ImageUploadService $imageUploadService,
        ProductRepository $productRepository
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hasPrimary = false;
            $formImages = $form->get('images');

            foreach ($formImages as $formIndex => $imageForm) {
                $image = $imageForm->getData();
                $uploadedFile = $imageForm->get('file')->getData();

                if ($uploadedFile && $image->getUploadType() === 'file') {
                    $newFilename = $imageUploadService->upload($uploadedFile, $product->getName());
                    $image->setUrl($imageUploadService->getUploadPath($newFilename));
                }

                if ($entityManager->contains($image)) {
                    $metadata = $entityManager->getClassMetadata(\App\Entity\Image::class);
                    $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($metadata, $image);
                } else {
                    $entityManager->persist($image);
                }

                if ($image->isPrimary()) {
                    if ($hasPrimary) {
                        $image->setIsPrimary(false);
                    } else {
                        $hasPrimary = true;
                    }
                }
            }

            if (!$hasPrimary && $product->getImages()->count() > 0) {
                $product->getImages()->first()->setIsPrimary(true);
            }

            $slug = $productRepository->generateUniqueSlug($product->getName());
            $product->setSlug($slug);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.products.messages.created'));

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/products/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'isEdit' => false,
        ]);
    }

    #[Route('/products/{id}/edit', name: 'product_edit')]
    public function productEdit(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        ImageUploadService $imageUploadService,
        ProductRepository $productRepository
    ): Response {
        $originalName = $product->getName();
        $originalImages = [];
        $originalPositions = [];
        foreach ($product->getImages() as $image) {
            $originalImages[] = $image;
            $originalPositions[$image->getId()] = $image->getPosition();
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($originalImages as $originalImage) {
                if (!$product->getImages()->contains($originalImage)) {
                    if ($originalImage->isFileType()) {
                        $filename = basename($originalImage->getUrl());
                        $imageUploadService->delete($filename);
                    }
                }
            }

            $hasPrimary = false;
            $formImages = $form->get('images');

            foreach ($formImages as $formIndex => $imageForm) {
                $image = $imageForm->getData();
                $uploadedFile = $imageForm->get('file')->getData();

                if ($uploadedFile && $image->getUploadType() === 'file') {
                    if ($image->getUrl() && $image->isFileType() && !str_starts_with($image->getUrl(), 'data:')) {
                        $oldFilename = basename($image->getUrl());
                        $imageUploadService->delete($oldFilename);
                    }

                    $newFilename = $imageUploadService->upload($uploadedFile, $product->getName());
                    $image->setUrl($imageUploadService->getUploadPath($newFilename));
                }

                $newPosition = $image->getPosition();

                if ($entityManager->contains($image)) {
                    $imageId = $image->getId();
                    $oldPosition = $originalPositions[$imageId] ?? null;

                    if ($oldPosition !== null && $oldPosition !== $newPosition) {
                        $uow = $entityManager->getUnitOfWork();
                        $uow->propertyChanged($image, 'position', $oldPosition, $newPosition);
                    }
                } else {
                    $entityManager->persist($image);
                }

                if ($image->isPrimary()) {
                    if ($hasPrimary) {
                        $image->setIsPrimary(false);
                    } else {
                        $hasPrimary = true;
                    }
                }
            }

            if (!$hasPrimary && $product->getImages()->count() > 0) {
                $product->getImages()->first()->setIsPrimary(true);
            }

            if ($product->getName() !== $originalName) {
                $slug = $productRepository->generateUniqueSlug($product->getName(), $product->getId());
                $product->setSlug($slug);
            }

            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.products.messages.updated'));

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/products/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'isEdit' => true,
        ]);
    }

    #[Route('/products/{id}/delete', name: 'product_delete', methods: ['POST'])]
    public function productDelete(
        Product $product,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        TranslatorInterface $translator
    ): Response {
        if (!$productRepository->canBeDeleted($product)) {
            $this->addFlash('error', $translator->trans('admin.products.messages.cannot_delete_has_orders'));
            return $this->redirectToRoute('app_admin_products');
        }

        try {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.products.messages.deleted'));
        } catch (\Exception $e) {
            $this->addFlash('error', $translator->trans('admin.products.messages.error'));
        }

        return $this->redirectToRoute('app_admin_products');
    }

    #[Route('/categories', name: 'categories')]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAllHierarchical();

        $categoriesData = [];
        foreach ($categories as $category) {
            $categoriesData[] = [
                'category' => $category,
                'productsCount' => $categoryRepository->countProducts($category),
                'childrenCount' => count($category->getChildren()),
            ];
        }

        return $this->render('admin/categories/index.html.twig', [
            'categoriesData' => $categoriesData,
        ]);
    }

    #[Route('/categories/new', name: 'category_new')]
    public function categoryNew(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.categories.messages.created'));

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/form.html.twig', [
            'form' => $form,
            'category' => $category,
            'isEdit' => false,
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'category_edit')]
    public function categoryEdit(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.categories.messages.updated'));

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/form.html.twig', [
            'form' => $form,
            'category' => $category,
            'isEdit' => true,
        ]);
    }

    #[Route('/categories/{id}/delete', name: 'category_delete', methods: ['POST'])]
    public function categoryDelete(
        Category $category,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        TranslatorInterface $translator
    ): Response {
        if (!$categoryRepository->canBeDeleted($category)) {
            if ($categoryRepository->hasChildren($category)) {
                $this->addFlash('error', $translator->trans('admin.categories.messages.cannot_delete_has_children'));
            } else {
                $this->addFlash('error', $translator->trans('admin.categories.messages.cannot_delete_has_products'));
            }
            return $this->redirectToRoute('app_admin_categories');
        }

        try {
            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.categories.messages.deleted'));
        } catch (\Exception $e) {
            $this->addFlash('error', $translator->trans('admin.categories.messages.error'));
        }

        return $this->redirectToRoute('app_admin_categories');
    }

    #[Route('/orders', name: 'orders')]
    public function orders(
        OrderRepository $orderRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $queryBuilder = $orderRepository->findAllOrderedByDateQueryBuilder();

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $pagination,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/orders/{id}', name: 'order_show')]
    public function orderShow(
        Order $order,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $form = $this->createForm(OrderStatusType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.orders.messages.status_updated'));

            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
        }

        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/users', name: 'users')]
    public function users(
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $queryBuilder = $userRepository->findAllOrderedQueryBuilder();

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        $usersData = [];
        foreach ($pagination as $user) {
            $usersData[] = [
                'user' => $user,
                'ordersCount' => $userRepository->countOrders($user),
            ];
        }

        return $this->render('admin/users/index.html.twig', [
            'usersData' => $usersData,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/users/new', name: 'user_new')]
    public function userNew(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        TranslatorInterface $translator
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.users.messages.created'));

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users/form.html.twig', [
            'form' => $form,
            'user' => $user,
            'isEdit' => false,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    public function userEdit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.users.messages.updated'));

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users/form.html.twig', [
            'form' => $form,
            'user' => $user,
            'isEdit' => true,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function userDelete(
        User $user,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        TranslatorInterface $translator
    ): Response {
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', $translator->trans('admin.users.messages.cannot_delete_self'));
            return $this->redirectToRoute('app_admin_users');
        }

        if (!$userRepository->canBeDeleted($user)) {
            if ($userRepository->countOrders($user) > 0) {
                $this->addFlash('error', $translator->trans('admin.users.messages.cannot_delete_has_orders'));
            } else {
                $this->addFlash('error', $translator->trans('admin.users.messages.cannot_delete_last_admin'));
            }
            return $this->redirectToRoute('app_admin_users');
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('admin.users.messages.deleted'));
        } catch (\Exception $e) {
            $this->addFlash('error', $translator->trans('admin.users.messages.error'));
        }

        return $this->redirectToRoute('app_admin_users');
    }
}
