<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFilterType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class ProductController extends AbstractController
{
    /**
     * @Route("/", name="productIndex", methods={"GET", "POST"})
     */
    public function index( ProductRepository $productRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $formFilter = $this->createForm(ProductFilterType::class);
        $formFilter->handleRequest($request);
        $filter = [];
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $filter = $formFilter->getData();
        }
        $product=$productRepository->getFilter($filter);
        $product = $paginator->paginate(
            $product, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            2 /*limit per page*/
        );
        return $this->render('product/index.html.twig', [
            'productsViews' => $product,
            'form' => $formFilter->createView(),
        ]);
    }

    /**
     * @Route("/newProduct", name="newProduct", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $product-> setCreatedAt(new \DateTime());
            $product->setStatus(true);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Successfully added');

            return $this->redirectToRoute('productIndex');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/showProduct/{id}/", name="productShow", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/editProduct/{id}/", name="productEdit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product-> setUpdatedAt(new \DateTime());
            $product->setStatus(true);
            $this->getDoctrine()->getManager()->flush();


            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('productIndex');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/deleteProduct/{id}", name="productDelete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $eM = $this->getDoctrine()->getManager();
            $product->setStatus(false);
            $eM->flush();
        }
        $this->addFlash('success', 'Successfully delete');
        return $this->redirectToRoute('productIndex');
    }









    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct( EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }

    private function getData(): array
    {
        /**
         * @var $product Product[]
         */
        $list = [];
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        foreach ($products as $product) {
            $list[] = [
                $product->getCode(),
                $product->getName(),
                $product->getDescription(),
                $product->getBrand(),
                $product->getPrice(),
                $product->getCategory()
            ];
        }
        return $list;
    }

    /**
     * @Route("/export",  name="exportProduct")
     */

    public function export(){
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Product list');

        $sheet->getCell('A1')->setValue('Code');
        $sheet->getCell('B1')->setValue('Name');
        $sheet->getCell('C1')->setValue('Description');
        $sheet->getCell('D1')->setValue('Brand');
        $sheet->getCell('E1')->setValue('Price');
        $sheet->getCell('F1')->setValue('Category');

        // Increase row cursor after header write
        $sheet->fromArray($this->getData(),null, 'A3', true);


        $writer = new Xlsx($spreadsheet);
        $fileName ='products.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);


        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }



}

